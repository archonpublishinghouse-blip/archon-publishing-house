<?php
namespace App\Controllers;

use App\Core\Security;

final class AdminController extends Controller {
    private array $resources = [
        'authors' => ['table' => 'authors', 'title' => 'Authors', 'fields' => ['name', 'slug', 'bio', 'website', 'is_active', 'is_featured']],
        'services' => ['table' => 'services', 'title' => 'Services', 'fields' => ['title', 'slug', 'summary', 'description', 'benefits', 'timeline', 'starting_price', 'display_order', 'is_active']],
        'posts' => ['table' => 'blog_posts', 'title' => 'Journal', 'fields' => ['title', 'slug', 'excerpt', 'body', 'status', 'published_at']],
        'settings' => ['table' => 'settings', 'title' => 'Site settings', 'fields' => ['setting_key', 'setting_value']],
        'reviews' => ['table' => 'reviews', 'title' => 'Reviews', 'fields' => ['rating', 'body', 'status'], 'creatable' => false],
        // Dormant marketplace resources remain reachable only by exact URL for future recovery work.
        'books' => ['table' => 'books', 'title' => 'eBooks', 'fields' => ['title', 'slug', 'author_id', 'price', 'sale_price', 'isbn', 'short_description', 'description', 'published_at', 'is_active', 'is_featured']],
        'categories' => ['table' => 'categories', 'title' => 'Categories', 'fields' => ['name', 'slug']],
        'genres' => ['table' => 'genres', 'title' => 'Genres', 'fields' => ['name', 'slug']],
        'coupons' => ['table' => 'coupons', 'title' => 'Coupons', 'fields' => ['code', 'discount_type', 'discount_value', 'starts_at', 'ends_at', 'is_active']],
        'contacts' => ['table' => 'contact_messages', 'title' => 'Contact messages', 'fields' => ['name', 'email', 'subject', 'message', 'status']],
        'quotes' => ['table' => 'quote_requests', 'title' => 'Quote requests', 'fields' => ['name', 'email', 'phone', 'book_title', 'description', 'status']],
    ];

    private array $leadStatuses = [
        'quote' => ['new', 'contacted', 'in_discussion', 'converted', 'rejected', 'closed'],
        'contact' => ['new', 'read', 'replied', 'archived'],
    ];

    public function dashboard(): never {
        $this->requireAdmin();
        $stats = [
            'new_quotes' => (int)($this->one("SELECT COUNT(*) count FROM quote_requests WHERE status='new'")['count'] ?? 0),
            'open_quotes' => (int)($this->one("SELECT COUNT(*) count FROM quote_requests WHERE status IN ('new','contacted','in_discussion')")['count'] ?? 0),
            'new_messages' => (int)($this->one("SELECT COUNT(*) count FROM contact_messages WHERE status='new'")['count'] ?? 0),
            'converted' => (int)($this->one("SELECT COUNT(*) count FROM quote_requests WHERE status='converted'")['count'] ?? 0),
        ];
        $recentLeads = $this->leadRows('all', '', '', 8);
        $this->render('admin/dashboard', compact('stats', 'recentLeads'));
    }

    public function leads(): never {
        $this->requireAdmin();
        $source = $_GET['source'] ?? 'all';
        if (!in_array($source, ['all', 'quotes', 'contacts'], true)) $source = 'all';
        $status = trim($_GET['status'] ?? '');
        $q = trim($_GET['q'] ?? '');

        $leads = $this->leadRows($source, $status, $q, 200);
        $metrics = $this->leadMetrics();
        $statusOptions = array_values(array_unique(array_merge($this->leadStatuses['quote'], $this->leadStatuses['contact'])));
        $this->render('admin/leads', compact('leads', 'metrics', 'source', 'status', 'q', 'statusOptions'));
    }

    public function leadDetail(string $kind, int $id): never {
        $admin = $this->requireAdmin();
        [$lead, $attachments, $notes] = $this->loadLead($kind, $id);
        if (!$lead) $this->render('errors/404', [], 404);
        $statuses = $this->leadStatuses[$kind];
        $this->render('admin/lead-detail', compact('admin', 'kind', 'lead', 'attachments', 'notes', 'statuses'));
    }

    public function updateLeadStatus(string $kind, int $id): never {
        $admin = $this->requireAdmin();
        $this->requirePost();
        if (!isset($this->leadStatuses[$kind])) $this->render('errors/404', [], 404);
        $status = $_POST['status'] ?? '';
        if (!in_array($status, $this->leadStatuses[$kind], true)) {
            Security::flash('error', 'Choose a valid lead status.');
            Security::redirect('/admin/leads/'.$kind.'/'.$id);
        }
        $table = $kind === 'quote' ? 'quote_requests' : 'contact_messages';
        $this->db()->prepare("UPDATE $table SET status=? WHERE id=?")->execute([$status, $id]);
        $this->logLeadActivity($admin, $kind, $id, 'lead_status', 'Status changed to '.$status.'.');
        Security::flash('success', 'Lead status updated.');
        Security::redirect('/admin/leads/'.$kind.'/'.$id);
    }

    public function addLeadNote(string $kind, int $id): never {
        $admin = $this->requireAdmin();
        $this->requirePost();
        if (!isset($this->leadStatuses[$kind])) $this->render('errors/404', [], 404);
        $body = trim($_POST['body'] ?? '');
        if (strlen($body) < 2) {
            Security::flash('error', 'Write a short note before saving.');
            Security::redirect('/admin/leads/'.$kind.'/'.$id);
        }
        if ($kind === 'quote') {
            $this->db()->prepare('INSERT INTO quote_notes(quote_request_id,admin_id,body) VALUES(?,?,?)')->execute([$id, $admin['id'], $body]);
        } else {
            $this->logLeadActivity($admin, $kind, $id, 'lead_note', $body);
        }
        Security::flash('success', 'Note added to the lead.');
        Security::redirect('/admin/leads/'.$kind.'/'.$id);
    }

    public function downloadQuoteAttachment(int $id): never {
        $this->requireAdmin();
        $attachment = $this->one('SELECT * FROM quote_attachments WHERE id=?', [$id]);
        if (!$attachment) $this->render('errors/404', [], 404);
        $base = realpath(dirname(__DIR__, 2).'/private/quote-attachments');
        $path = $base ? realpath($base.'/'.$attachment['storage_name']) : false;
        if (!$path || !str_starts_with($path, $base.DIRECTORY_SEPARATOR) || !is_file($path)) $this->render('errors/404', [], 404);
        $downloadName = preg_replace('/[^A-Za-z0-9._ -]/', '_', basename($attachment['original_name'])) ?: 'archon-attachment';
        header('Content-Type: '.($attachment['mime_type'] ?: 'application/octet-stream'));
        header('Content-Length: '.filesize($path));
        header('Content-Disposition: attachment; filename="'.$downloadName.'"');
        readfile($path);
        exit;
    }

    public function bookFiles(): never {
        $this->requireAdmin();
        $books = $this->all('SELECT id,title FROM books ORDER BY title');
        $files = $this->all('SELECT bf.*,b.title FROM book_files bf JOIN books b ON b.id=bf.book_id ORDER BY bf.created_at DESC');
        $this->render('admin/book-files', compact('books', 'files'));
    }

    public function uploadBookFile(): never {
        $this->requireAdmin();
        $this->requirePost();
        $bookId = (int)($_POST['book_id'] ?? 0);
        $book = $this->one('SELECT id FROM books WHERE id=?', [$bookId]);
        $file = $_FILES['file'] ?? null;
        if (!$book || !$file || $file['error'] !== UPLOAD_ERR_OK) {
            Security::flash('error', 'Choose a valid book and upload file.');
            Security::redirect('/admin/book-files');
        }
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['pdf' => ['application/pdf'], 'epub' => ['application/epub+zip', 'application/zip']];
        $max = (int)(\App\Core\Env::get('MAX_BOOK_FILE_MB', '100')) * 1024 * 1024;
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
        if (!isset($allowed[$extension]) || !in_array($mime, $allowed[$extension], true) || $file['size'] > $max) {
            Security::flash('error', 'Only PDF or EPUB files within the configured size limit are accepted.');
            Security::redirect('/admin/book-files');
        }
        $stored = bin2hex(random_bytes(24)).'.'.$extension;
        $target = dirname(__DIR__, 2).'/private/books/'.$stored;
        if (!move_uploaded_file($file['tmp_name'], $target)) {
            Security::flash('error', 'The eBook could not be saved to private storage.');
            Security::redirect('/admin/book-files');
        }
        $this->db()->prepare('INSERT INTO book_files(book_id,format,storage_name,original_name,file_size) VALUES(?,?,?,?,?)')->execute([$bookId, $extension, $stored, basename($file['name']), $file['size']]);
        Security::flash('success', 'The eBook file is protected in private storage.');
        Security::redirect('/admin/book-files');
    }

    public function deleteBookFile(): never {
        $this->requireAdmin();
        $this->requirePost();
        $id = (int)($_POST['id'] ?? 0);
        $file = $this->one('SELECT * FROM book_files WHERE id=?', [$id]);
        if (!$file) {
            Security::flash('error', 'File record not found.');
            Security::redirect('/admin/book-files');
        }
        $base = realpath(dirname(__DIR__, 2).'/private/books');
        $path = $base ? realpath($base.'/'.$file['storage_name']) : false;
        if ($path && str_starts_with($path, $base.DIRECTORY_SEPARATOR)) unlink($path);
        $this->db()->prepare('DELETE FROM book_files WHERE id=?')->execute([$id]);
        Security::flash('success', 'Protected eBook file removed.');
        Security::redirect('/admin/book-files');
    }

    public function export(string $kind): never {
        $this->requireAdmin();
        $map = [
            'quotes' => ['quote_requests', ['name', 'email', 'phone', 'book_title', 'genre', 'word_count', 'project_stage', 'budget_range', 'status', 'created_at']],
            'contacts' => ['contact_messages', ['name', 'email', 'phone', 'subject', 'message', 'status', 'created_at']],
            'subscribers' => ['newsletter_subscribers', ['email', 'status', 'consented_at', 'created_at']],
        ];
        $spec = $map[$kind] ?? null;
        if (!$spec) $this->render('errors/404', [], 404);
        $rows = $this->all('SELECT '.implode(',', $spec[1]).' FROM '.$spec[0].' ORDER BY id DESC');
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="archon-'.$kind.'-'.date('Ymd').'.csv"');
        $out = fopen('php://output', 'w');
        fputcsv($out, $spec[1]);
        foreach ($rows as $row) fputcsv($out, $row);
        fclose($out);
        exit;
    }

    public function resource(string $name): never {
        $this->requireAdmin();
        $cfg = $this->resources[$name] ?? null;
        if (!$cfg) $this->render('errors/404', [], 404);
        $q = trim($_GET['q'] ?? '');
        $sql = 'SELECT * FROM '.$cfg['table'];
        $params = [];
        if ($q !== '' && in_array('title', $cfg['fields'], true)) {
            $sql .= ' WHERE title LIKE ?';
            $params = ['%'.$q.'%'];
        }
        $rows = $this->all($sql.' ORDER BY id DESC LIMIT 100', $params);
        $this->render('admin/resource', compact('cfg', 'name', 'rows'));
    }

    public function mutate(string $name, string $action): never {
        $this->requireAdmin();
        $this->requirePost();
        $cfg = $this->resources[$name] ?? null;
        if (!$cfg) $this->render('errors/404', [], 404);
        $id = (int)($_POST['id'] ?? 0);
        if ($action === 'delete') {
            $this->db()->prepare('DELETE FROM '.$cfg['table'].' WHERE id=?')->execute([$id]);
            Security::flash('success', 'Record removed.');
            Security::redirect('/admin/'.$name);
        }
        $data = [];
        foreach ($cfg['fields'] as $field) {
            if (array_key_exists($field, $_POST)) $data[$field] = is_string($_POST[$field]) ? trim($_POST[$field]) : $_POST[$field];
        }
        if ($cfg['table'] === 'books' && empty($data['published_at'])) $data['published_at'] = date('Y-m-d');
        if (!$data) {
            Security::flash('error', 'No values supplied.');
            Security::redirect('/admin/'.$name);
        }
        if ($action === 'create') {
            $keys = array_keys($data);
            $this->db()->prepare('INSERT INTO '.$cfg['table'].' ('.implode(',', $keys).') VALUES ('.implode(',', array_fill(0, count($keys), '?')).')')->execute(array_values($data));
        } else {
            $set = implode(',', array_map(fn($k) => "$k=?", array_keys($data)));
            $this->db()->prepare('UPDATE '.$cfg['table']." SET $set WHERE id=?")->execute([...array_values($data), $id]);
        }
        Security::flash('success', 'Record saved.');
        Security::redirect('/admin/'.$name);
    }

    private function leadRows(string $source, string $status, string $q, int $limit): array {
        $parts = [];
        $params = [];
        $includeQuotes = ($source === 'all' || $source === 'quotes') && ($status === '' || in_array($status, $this->leadStatuses['quote'], true));
        $includeContacts = ($source === 'all' || $source === 'contacts') && ($status === '' || in_array($status, $this->leadStatuses['contact'], true));
        if ($includeQuotes) {
            $where = [];
            if ($status !== '' && in_array($status, $this->leadStatuses['quote'], true)) {
                $where[] = 'qr.status=?';
                $params[] = $status;
            }
            if ($q !== '') {
                $where[] = '(qr.name LIKE ? OR qr.email LIKE ? OR qr.phone LIKE ? OR qr.book_title LIKE ? OR qr.description LIKE ?)';
                array_push($params, ...array_fill(0, 5, '%'.$q.'%'));
            }
            $parts[] = "SELECT 'quote' source, qr.id, qr.name, qr.email, qr.phone, qr.status, qr.created_at, qr.updated_at, qr.book_title title, qr.description body, qr.genre, s.title service_title FROM quote_requests qr LEFT JOIN services s ON s.id=qr.service_id".($where ? ' WHERE '.implode(' AND ', $where) : '');
        }
        if ($includeContacts) {
            $where = [];
            if ($status !== '' && in_array($status, $this->leadStatuses['contact'], true)) {
                $where[] = 'cm.status=?';
                $params[] = $status;
            }
            if ($q !== '') {
                $where[] = '(cm.name LIKE ? OR cm.email LIKE ? OR cm.phone LIKE ? OR cm.subject LIKE ? OR cm.message LIKE ?)';
                array_push($params, ...array_fill(0, 5, '%'.$q.'%'));
            }
            $parts[] = "SELECT 'contact' source, cm.id, cm.name, cm.email, cm.phone, cm.status, cm.created_at, cm.updated_at, cm.subject title, cm.message body, NULL genre, NULL service_title FROM contact_messages cm".($where ? ' WHERE '.implode(' AND ', $where) : '');
        }
        if (!$parts) return [];
        return $this->all('SELECT * FROM ('.implode(' UNION ALL ', $parts).') leads ORDER BY created_at DESC LIMIT '.max(1, $limit), $params);
    }

    private function leadMetrics(): array {
        return [
            'quotes_total' => (int)($this->one('SELECT COUNT(*) count FROM quote_requests')['count'] ?? 0),
            'quotes_new' => (int)($this->one("SELECT COUNT(*) count FROM quote_requests WHERE status='new'")['count'] ?? 0),
            'contacts_total' => (int)($this->one('SELECT COUNT(*) count FROM contact_messages')['count'] ?? 0),
            'contacts_new' => (int)($this->one("SELECT COUNT(*) count FROM contact_messages WHERE status='new'")['count'] ?? 0),
        ];
    }

    private function loadLead(string $kind, int $id): array {
        if (!isset($this->leadStatuses[$kind])) return [null, [], []];
        if ($kind === 'quote') {
            $lead = $this->one('SELECT qr.*,s.title service_title FROM quote_requests qr LEFT JOIN services s ON s.id=qr.service_id WHERE qr.id=?', [$id]);
            $attachments = $this->all('SELECT * FROM quote_attachments WHERE quote_request_id=? ORDER BY created_at DESC', [$id]);
            $notes = $this->all('SELECT qn.body,qn.created_at,a.name admin_name FROM quote_notes qn LEFT JOIN admins a ON a.id=qn.admin_id WHERE qn.quote_request_id=? ORDER BY qn.created_at DESC', [$id]);
            return [$lead, $attachments, $notes];
        }
        $lead = $this->one('SELECT * FROM contact_messages WHERE id=?', [$id]);
        $notes = $this->all("SELECT al.details body,al.created_at,a.name admin_name FROM activity_logs al LEFT JOIN admins a ON a.id=al.admin_id WHERE al.action='lead_note' AND al.entity_type='contact_message' AND al.entity_id=? ORDER BY al.created_at DESC", [$id]);
        return [$lead, [], $notes];
    }

    private function logLeadActivity(array $admin, string $kind, int $id, string $action, string $details): void {
        $entityType = $kind === 'quote' ? 'quote_request' : 'contact_message';
        $this->db()->prepare('INSERT INTO activity_logs(admin_id,action,entity_type,entity_id,details) VALUES(?,?,?,?,?)')->execute([$admin['id'], $action, $entityType, $id, $details]);
    }
}
