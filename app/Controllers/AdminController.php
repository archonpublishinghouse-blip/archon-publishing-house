<?php
namespace App\Controllers;

use App\Core\Security;
use App\Services\CrmSchemaService;

final class AdminController extends Controller {
    private array $resources = [
        'authors' => ['table' => 'authors', 'title' => 'Authors', 'fields' => ['name', 'slug', 'bio', 'website', 'is_active', 'is_featured']],
        'services' => ['table' => 'services', 'title' => 'Services', 'fields' => ['title', 'slug', 'summary', 'description', 'benefits', 'timeline', 'starting_price', 'display_order', 'is_active']],
        'posts' => ['table' => 'blog_posts', 'title' => 'Journal', 'fields' => ['title', 'slug', 'excerpt', 'body', 'status', 'published_at']],
        'settings' => ['table' => 'settings', 'title' => 'Site settings', 'fields' => ['setting_key', 'setting_value']],
        'reviews' => ['table' => 'reviews', 'title' => 'Reviews', 'fields' => ['rating', 'body', 'status'], 'creatable' => false],
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
        $admin = $this->requireCrmUser();
        $canManageAll = $this->canManageAllLeads($admin);
        $stats = $this->dashboardStats($admin);
        $recentLeads = $this->leadRows('all', '', '', '', 8, $admin);
        $this->render('admin/dashboard', compact('admin', 'canManageAll', 'stats', 'recentLeads'));
    }

    public function leads(): never {
        $admin = $this->requireCrmUser();
        $source = $_GET['source'] ?? 'all';
        if (!in_array($source, ['all', 'quotes', 'contacts'], true)) $source = 'all';
        $status = trim($_GET['status'] ?? '');
        $q = trim($_GET['q'] ?? '');
        $assigned = trim($_GET['assigned'] ?? '');
        if (!$this->canManageAllLeads($admin)) $assigned = 'me';
        $leads = $this->leadRows($source, $status, $q, $assigned, 200, $admin);
        $metrics = $this->leadMetrics($admin);
        $statusOptions = array_values(array_unique(array_merge($this->leadStatuses['quote'], $this->leadStatuses['contact'])));
        $employees = $this->crmUsers();
        $canManageAll = $this->canManageAllLeads($admin);
        $this->render('admin/leads', compact('admin', 'canManageAll', 'leads', 'metrics', 'source', 'status', 'q', 'assigned', 'statusOptions', 'employees'));
    }

    public function leadDetail(string $kind, int $id): never {
        $admin = $this->requireCrmUser();
        [$lead, $attachments, $notes] = $this->loadLead($kind, $id, $admin);
        if (!$lead) $this->render('errors/404', [], 404);
        $statuses = $this->leadStatuses[$kind];
        $employees = $this->crmUsers();
        $canManageAll = $this->canManageAllLeads($admin);
        $this->render('admin/lead-detail', compact('admin', 'canManageAll', 'kind', 'lead', 'attachments', 'notes', 'statuses', 'employees'));
    }

    public function updateLeadStatus(string $kind, int $id): never {
        $admin = $this->requireCrmUser();
        $this->requirePost();
        if (!isset($this->leadStatuses[$kind])) $this->render('errors/404', [], 404);
        [$lead] = $this->loadLead($kind, $id, $admin);
        if (!$lead) $this->render('errors/404', [], 404);
        $status = $_POST['status'] ?? '';
        if (!in_array($status, $this->leadStatuses[$kind], true)) {
            Security::flash('error', 'Choose a valid lead status.');
            Security::redirect('/admin/leads/'.$kind.'/'.$id);
        }
        $table = $kind === 'quote' ? 'quote_requests' : 'contact_messages';
        $this->db()->prepare("UPDATE $table SET status=? WHERE id=?")->execute([$status, $id]);
        $this->logLeadActivity($admin, $kind, $id, 'lead_status', 'Status changed from '.($lead['status'] ?? 'unknown').' to '.$status.'.');
        Security::flash('success', 'Lead status updated.');
        Security::redirect('/admin/leads/'.$kind.'/'.$id);
    }

    public function assignLead(string $kind, int $id): never {
        $admin = $this->requireLeadManager();
        $this->requirePost();
        if (!isset($this->leadStatuses[$kind])) $this->render('errors/404', [], 404);
        [$lead] = $this->loadLead($kind, $id, $admin);
        if (!$lead) $this->render('errors/404', [], 404);
        $assignedId = (int)($_POST['assigned_admin_id'] ?? 0);
        $assignee = $assignedId > 0 ? $this->one("SELECT id,name FROM admins WHERE id=? AND is_active=1 AND role IN ('admin','super_admin','employee')", [$assignedId]) : null;
        if ($assignedId > 0 && !$assignee) {
            Security::flash('error', 'Choose a valid active employee.');
            Security::redirect('/admin/leads/'.$kind.'/'.$id);
        }
        $table = $kind === 'quote' ? 'quote_requests' : 'contact_messages';
        $this->db()->prepare("UPDATE $table SET assigned_admin_id=?, assigned_at=? WHERE id=?")->execute([$assignee['id'] ?? null, $assignee ? date('Y-m-d H:i:s') : null, $id]);
        $this->logLeadActivity($admin, $kind, $id, 'lead_assignment', $assignee ? 'Assigned to '.$assignee['name'].'.' : 'Lead assignment cleared.');
        Security::flash('success', $assignee ? 'Lead assigned to '.$assignee['name'].'.' : 'Lead moved back to unassigned.');
        Security::redirect('/admin/leads/'.$kind.'/'.$id);
    }

    public function addLeadNote(string $kind, int $id): never {
        $admin = $this->requireCrmUser();
        $this->requirePost();
        if (!isset($this->leadStatuses[$kind])) $this->render('errors/404', [], 404);
        [$lead] = $this->loadLead($kind, $id, $admin);
        if (!$lead) $this->render('errors/404', [], 404);
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

    public function employees(): never {
        $admin = $this->requireLeadManager();
        $employees = $this->all("SELECT id,name,email,phone,job_title,role,is_active,last_login_at,created_at FROM admins ORDER BY role='employee' DESC, name");
        $this->render('admin/employees', compact('admin', 'employees'));
    }

    public function createEmployee(): never {
        $this->requireLeadManager();
        $this->requirePost();
        $name = trim($_POST['name'] ?? '');
        $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
        $password = $_POST['password'] ?? '';
        $role = in_array($_POST['role'] ?? 'employee', ['admin', 'employee'], true) ? $_POST['role'] : 'employee';
        if (strlen($name) < 2 || !$email || strlen($password) < 10 || !preg_match('/[A-Za-z]/', $password) || !preg_match('/\d/', $password)) {
            Security::flash('error', 'Use a name, valid email, and a 10-character password with letters and numbers.');
            Security::redirect('/admin/employees');
        }
        try {
            $this->db()->prepare('INSERT INTO admins(name,email,phone,job_title,password_hash,role,is_active) VALUES(?,?,?,?,?,?,1)')->execute([
                $name, $email, trim($_POST['phone'] ?? ''), trim($_POST['job_title'] ?? ''), password_hash($password, PASSWORD_DEFAULT), $role,
            ]);
        } catch (\PDOException) {
            Security::flash('error', 'That email is already used by another team member.');
            Security::redirect('/admin/employees');
        }
        Security::flash('success', 'Team account created.');
        Security::redirect('/admin/employees');
    }

    public function updateEmployee(int $id): never {
        $admin = $this->requireLeadManager();
        $this->requirePost();
        $employee = $this->one('SELECT * FROM admins WHERE id=?', [$id]);
        if (!$employee) $this->render('errors/404', [], 404);
        $role = in_array($_POST['role'] ?? 'employee', ['admin', 'employee'], true) ? $_POST['role'] : 'employee';
        $isActive = !empty($_POST['is_active']) ? 1 : 0;
        if ((int)$admin['id'] === $id && (!$isActive || $role !== 'admin')) {
            Security::flash('error', 'You cannot remove your own admin access.');
            Security::redirect('/admin/employees');
        }
        $password = $_POST['password'] ?? '';
        if ($password !== '' && (strlen($password) < 10 || !preg_match('/[A-Za-z]/', $password) || !preg_match('/\d/', $password))) {
            Security::flash('error', 'New passwords must be at least 10 characters and include letters and numbers.');
            Security::redirect('/admin/employees');
        }
        $params = [trim($_POST['name'] ?? $employee['name']), trim($_POST['phone'] ?? ''), trim($_POST['job_title'] ?? ''), $role, $isActive];
        $sql = 'UPDATE admins SET name=?, phone=?, job_title=?, role=?, is_active=?';
        if ($password !== '') {
            $sql .= ', password_hash=?';
            $params[] = password_hash($password, PASSWORD_DEFAULT);
        }
        $params[] = $id;
        $this->db()->prepare($sql.' WHERE id=?')->execute($params);
        Security::flash('success', 'Team account updated.');
        Security::redirect('/admin/employees');
    }

    public function downloadQuoteAttachment(int $id): never {
        $admin = $this->requireCrmUser();
        $attachment = $this->one('SELECT * FROM quote_attachments WHERE id=?', [$id]);
        if (!$attachment) $this->render('errors/404', [], 404);
        [$lead] = $this->loadLead('quote', (int)$attachment['quote_request_id'], $admin);
        if (!$lead) $this->render('errors/404', [], 404);
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
        $this->requireLeadManager();
        $books = $this->all('SELECT id,title FROM books ORDER BY title');
        $files = $this->all('SELECT bf.*,b.title FROM book_files bf JOIN books b ON b.id=bf.book_id ORDER BY bf.created_at DESC');
        $this->render('admin/book-files', compact('books', 'files'));
    }

    public function uploadBookFile(): never {
        $this->requireLeadManager();
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
        $this->requireLeadManager();
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
        $this->requireLeadManager();
        $map = [
            'quotes' => ['quote_requests qr LEFT JOIN admins a ON a.id=qr.assigned_admin_id', ['qr.name', 'qr.email', 'qr.phone', 'qr.book_title', 'qr.genre', 'qr.word_count', 'qr.project_stage', 'qr.budget_range', 'qr.status', 'a.name assigned_to', 'qr.created_at'], 'qr.created_at', ['name', 'email', 'phone', 'book_title', 'genre', 'word_count', 'project_stage', 'budget_range', 'status', 'assigned_to', 'created_at']],
            'contacts' => ['contact_messages cm LEFT JOIN admins a ON a.id=cm.assigned_admin_id', ['cm.name', 'cm.email', 'cm.phone', 'cm.subject', 'cm.message', 'cm.status', 'a.name assigned_to', 'cm.created_at'], 'cm.created_at', ['name', 'email', 'phone', 'subject', 'message', 'status', 'assigned_to', 'created_at']],
            'subscribers' => ['newsletter_subscribers', ['email', 'status', 'consented_at', 'created_at'], 'created_at', ['email', 'status', 'consented_at', 'created_at']],
        ];
        $spec = $map[$kind] ?? null;
        if (!$spec) $this->render('errors/404', [], 404);
        $rows = $this->all('SELECT '.implode(',', $spec[1]).' FROM '.$spec[0].' ORDER BY '.$spec[2].' DESC');
        $headers = $spec[3];
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="archon-'.$kind.'-'.date('Ymd').'.csv"');
        $out = fopen('php://output', 'w');
        fputcsv($out, $headers);
        foreach ($rows as $row) fputcsv($out, $row);
        fclose($out);
        exit;
    }

    public function resource(string $name): never {
        $this->requireLeadManager();
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
        $this->requireLeadManager();
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

    private function requireCrmUser(): array {
        $sessionAdmin = $this->requireAdmin();
        CrmSchemaService::ensure($this->db());
        $admin = $this->one('SELECT id,name,email,role,is_active FROM admins WHERE id=? AND is_active=1', [(int)$sessionAdmin['id']]);
        if (!$admin) {
            unset($_SESSION['admin']);
            Security::redirect('/admin/login');
        }
        $_SESSION['admin'] = ['id' => $admin['id'], 'name' => $admin['name'], 'email' => $admin['email'], 'role' => $admin['role'] ?: 'admin'];
        return $_SESSION['admin'];
    }

    private function requireLeadManager(): array {
        $admin = $this->requireCrmUser();
        if (!$this->canManageAllLeads($admin)) $this->render('errors/404', [], 404);
        return $admin;
    }

    private function canManageAllLeads(array $admin): bool {
        return in_array($admin['role'] ?? '', ['admin', 'super_admin'], true);
    }

    private function crmUsers(): array {
        return $this->all("SELECT id,name,email,role,is_active FROM admins WHERE is_active=1 AND role IN ('admin','super_admin','employee') ORDER BY role='employee' DESC, name");
    }

    private function leadRows(string $source, string $status, string $q, string $assigned, int $limit, array $admin): array {
        $parts = [];
        $params = [];
        $includeQuotes = ($source === 'all' || $source === 'quotes') && ($status === '' || in_array($status, $this->leadStatuses['quote'], true));
        $includeContacts = ($source === 'all' || $source === 'contacts') && ($status === '' || in_array($status, $this->leadStatuses['contact'], true));
        if ($includeQuotes) {
            [$where, $whereParams] = $this->leadWhere('quote', $status, $q, $assigned, $admin);
            $params = array_merge($params, $whereParams);
            $parts[] = "SELECT 'quote' source, qr.id, qr.name, qr.email, qr.phone, qr.status, qr.assigned_admin_id, assignee.name assigned_name, qr.priority, qr.created_at, qr.updated_at, qr.book_title title, qr.description body, qr.genre, s.title service_title FROM quote_requests qr LEFT JOIN services s ON s.id=qr.service_id LEFT JOIN admins assignee ON assignee.id=qr.assigned_admin_id".($where ? ' WHERE '.implode(' AND ', $where) : '');
        }
        if ($includeContacts) {
            [$where, $whereParams] = $this->leadWhere('contact', $status, $q, $assigned, $admin);
            $params = array_merge($params, $whereParams);
            $parts[] = "SELECT 'contact' source, cm.id, cm.name, cm.email, cm.phone, cm.status, cm.assigned_admin_id, assignee.name assigned_name, cm.priority, cm.created_at, cm.updated_at, cm.subject title, cm.message body, NULL genre, NULL service_title FROM contact_messages cm LEFT JOIN admins assignee ON assignee.id=cm.assigned_admin_id".($where ? ' WHERE '.implode(' AND ', $where) : '');
        }
        if (!$parts) return [];
        return $this->all('SELECT * FROM ('.implode(' UNION ALL ', $parts).') leads ORDER BY created_at DESC LIMIT '.max(1, $limit), $params);
    }

    private function leadWhere(string $kind, string $status, string $q, string $assigned, array $admin): array {
        $prefix = $kind === 'quote' ? 'qr' : 'cm';
        $where = [];
        $params = [];
        if ($status !== '' && in_array($status, $this->leadStatuses[$kind], true)) {
            $where[] = "$prefix.status=?";
            $params[] = $status;
        }
        if (!$this->canManageAllLeads($admin)) {
            $where[] = "$prefix.assigned_admin_id=?";
            $params[] = (int)$admin['id'];
        } elseif ($assigned === 'unassigned') {
            $where[] = "$prefix.assigned_admin_id IS NULL";
        } elseif ($assigned === 'me') {
            $where[] = "$prefix.assigned_admin_id=?";
            $params[] = (int)$admin['id'];
        } elseif (preg_match('/^[0-9]+$/', $assigned)) {
            $where[] = "$prefix.assigned_admin_id=?";
            $params[] = (int)$assigned;
        }
        if ($q !== '') {
            $where[] = $kind === 'quote'
                ? '(qr.name LIKE ? OR qr.email LIKE ? OR qr.phone LIKE ? OR qr.book_title LIKE ? OR qr.description LIKE ?)'
                : '(cm.name LIKE ? OR cm.email LIKE ? OR cm.phone LIKE ? OR cm.subject LIKE ? OR cm.message LIKE ?)';
            array_push($params, ...array_fill(0, 5, '%'.$q.'%'));
        }
        return [$where, $params];
    }

    private function dashboardStats(array $admin): array {
        $assignedSql = $this->canManageAllLeads($admin) ? '' : ' AND assigned_admin_id='.(int)$admin['id'];
        return [
            'new_quotes' => (int)($this->one("SELECT COUNT(*) count FROM quote_requests WHERE status='new'$assignedSql")['count'] ?? 0),
            'open_quotes' => (int)($this->one("SELECT COUNT(*) count FROM quote_requests WHERE status IN ('new','contacted','in_discussion')$assignedSql")['count'] ?? 0),
            'new_messages' => (int)($this->one("SELECT COUNT(*) count FROM contact_messages WHERE status='new'$assignedSql")['count'] ?? 0),
            'converted' => (int)($this->one("SELECT COUNT(*) count FROM quote_requests WHERE status='converted'$assignedSql")['count'] ?? 0),
        ];
    }

    private function leadMetrics(array $admin): array {
        $assignedSql = $this->canManageAllLeads($admin) ? '' : ' WHERE assigned_admin_id='.(int)$admin['id'];
        $quoteNewWhere = $this->canManageAllLeads($admin) ? "WHERE status='new'" : "WHERE status='new' AND assigned_admin_id=".(int)$admin['id'];
        $contactNewWhere = $this->canManageAllLeads($admin) ? "WHERE status='new'" : "WHERE status='new' AND assigned_admin_id=".(int)$admin['id'];
        return [
            'quotes_total' => (int)($this->one('SELECT COUNT(*) count FROM quote_requests'.$assignedSql)['count'] ?? 0),
            'quotes_new' => (int)($this->one("SELECT COUNT(*) count FROM quote_requests $quoteNewWhere")['count'] ?? 0),
            'contacts_total' => (int)($this->one('SELECT COUNT(*) count FROM contact_messages'.$assignedSql)['count'] ?? 0),
            'contacts_new' => (int)($this->one("SELECT COUNT(*) count FROM contact_messages $contactNewWhere")['count'] ?? 0),
        ];
    }

    private function loadLead(string $kind, int $id, array $admin): array {
        if (!isset($this->leadStatuses[$kind])) return [null, [], []];
        $accessSql = $this->canManageAllLeads($admin) ? '' : ' AND '.($kind === 'quote' ? 'qr' : 'cm').'.assigned_admin_id='.(int)$admin['id'];
        if ($kind === 'quote') {
            $lead = $this->one('SELECT qr.*,s.title service_title,assignee.name assigned_name,assignee.email assigned_email FROM quote_requests qr LEFT JOIN services s ON s.id=qr.service_id LEFT JOIN admins assignee ON assignee.id=qr.assigned_admin_id WHERE qr.id=?'.$accessSql, [$id]);
            $attachments = $lead ? $this->all('SELECT * FROM quote_attachments WHERE quote_request_id=? ORDER BY created_at DESC', [$id]) : [];
            $notes = $lead ? $this->leadTimeline($kind, $id) : [];
            return [$lead, $attachments, $notes];
        }
        $lead = $this->one('SELECT cm.*,assignee.name assigned_name,assignee.email assigned_email FROM contact_messages cm LEFT JOIN admins assignee ON assignee.id=cm.assigned_admin_id WHERE cm.id=?'.$accessSql, [$id]);
        $notes = $lead ? $this->leadTimeline($kind, $id) : [];
        return [$lead, [], $notes];
    }

    private function leadTimeline(string $kind, int $id): array {
        $entityType = $kind === 'quote' ? 'quote_request' : 'contact_message';
        if ($kind === 'quote') {
            return $this->all("
                SELECT 'lead_note' action, qn.body, qn.created_at, a.name admin_name
                FROM quote_notes qn LEFT JOIN admins a ON a.id=qn.admin_id
                WHERE qn.quote_request_id=?
                UNION ALL
                SELECT al.action, al.details body, al.created_at, a.name admin_name
                FROM activity_logs al LEFT JOIN admins a ON a.id=al.admin_id
                WHERE al.entity_type=? AND al.entity_id=?
                ORDER BY created_at DESC
            ", [$id, $entityType, $id]);
        }
        return $this->all('SELECT al.action, al.details body, al.created_at, a.name admin_name FROM activity_logs al LEFT JOIN admins a ON a.id=al.admin_id WHERE al.entity_type=? AND al.entity_id=? ORDER BY al.created_at DESC', [$entityType, $id]);
    }

    private function logLeadActivity(array $admin, string $kind, int $id, string $action, string $details): void {
        $entityType = $kind === 'quote' ? 'quote_request' : 'contact_message';
        $this->db()->prepare('INSERT INTO activity_logs(admin_id,action,entity_type,entity_id,details) VALUES(?,?,?,?,?)')->execute([$admin['id'], $action, $entityType, $id, $details]);
    }
}
