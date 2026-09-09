<?php
namespace App\Services;

use App\Core\Database;
use PDO;

final class PackageService {
    public const SETTING_KEY = 'writing.packages';
    public const IDS = ['basic', 'medium', 'premium'];

    public static function defaults(): array {
        return [
            'heading' => 'Find the right writing plan for your next chapter.',
            'intro' => 'Choose a starting point for the conversation. Each package can be scoped around your material, intended reader and the help your eBook needs.',
            'note' => 'Final fees, manuscript length, timeline and revision scope are confirmed in your proposal before work begins.',
            'featured' => 'medium',
            'packages' => [
                'basic' => [
                    'name' => 'Basic', 'tagline' => 'Find your direction',
                    'description' => 'Bring focus to your idea and build a practical outline before developing the full manuscript.',
                    'audience' => 'For an idea, notes or an early outline.',
                    'price' => 'Request a quote', 'timeline' => 'Schedule agreed after consultation',
                    'features' => "Project discovery and reader definition\nBook purpose and positioning\nChapter outline development\nWriting direction and next-step guidance",
                    'button' => 'Discuss Basic', 'badge' => '', 'order' => 1, 'enabled' => true,
                ],
                'medium' => [
                    'name' => 'Medium', 'tagline' => 'Develop your manuscript',
                    'description' => 'Turn your outline and source material into connected chapters with collaborative writing support.',
                    'audience' => 'For authors ready to develop their draft.',
                    'price' => 'Request a quote', 'timeline' => 'Milestones agreed for your manuscript',
                    'features' => "Project discovery and chapter planning\nCollaborative eBook writing\nAuthor voice and chapter development\nClient review at agreed milestones\nEditorial refinement within the agreed scope",
                    'button' => 'Discuss Medium', 'badge' => 'Writing & editorial support', 'order' => 2, 'enabled' => true,
                ],
                'premium' => [
                    'name' => 'Premium', 'tagline' => 'Bring the work together',
                    'description' => 'Connect the writing, editorial and formatting stages in a coordinated plan for your completed eBook.',
                    'audience' => 'For projects needing writing through preparation.',
                    'price' => 'Request a quote', 'timeline' => 'A complete project schedule, tailored to you',
                    'features' => "Book positioning and chapter architecture\nCollaborative manuscript development\nEditorial review and manuscript polish\nClient review at agreed milestones\neBook formatting and finishing guidance\nPreparation for the next publishing step",
                    'button' => 'Discuss Premium', 'badge' => '', 'order' => 3, 'enabled' => true,
                ],
            ],
        ];
    }

    public static function get(?PDO $pdo = null): array {
        try {
            $statement = ($pdo ?? Database::pdo())->prepare('SELECT setting_value FROM settings WHERE setting_key=?');
            $statement->execute([self::SETTING_KEY]);
            $json = $statement->fetchColumn();
            if (!is_string($json) || $json === '') return self::defaults();
            $input = json_decode($json, true, 32, JSON_THROW_ON_ERROR);
            [$config, $errors] = self::validate($input);
            return $errors ? self::defaults() : $config;
        } catch (\Throwable) {
            return self::defaults();
        }
    }

    public static function validate(mixed $input): array {
        $errors = [];
        if (!is_array($input)) return [self::defaults(), ['Submit a valid package configuration.']];
        $read = static function (array $source, string $key, int $max, string $label, bool $required = true) use (&$errors): string {
            $value = $source[$key] ?? '';
            if (!is_string($value)) {
                $errors[] = $label . ' must be plain text.';
                return '';
            }
            $value = trim(str_replace(["\r\n", "\r"], "\n", $value));
            if ($required && $value === '') $errors[] = $label . ' is required.';
            if (preg_match_all('/./us', $value) > $max) $errors[] = $label . ' must be no longer than ' . $max . ' characters.';
            if (!preg_match('//u', $value) || preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', $value)) $errors[] = $label . ' contains unsupported characters.';
            return $value;
        };
        $config = [
            'heading' => $read($input, 'heading', 180, 'Section heading'),
            'intro' => $read($input, 'intro', 900, 'Section introduction'),
            'note' => $read($input, 'note', 500, 'Section note', false),
            'featured' => is_string($input['featured'] ?? null) ? $input['featured'] : '',
            'packages' => [],
        ];
        if (!is_string($input['featured'] ?? null) || !in_array($config['featured'], ['', ...self::IDS], true)) $errors[] = 'Choose a valid featured package, or None.';
        $items = $input['packages'] ?? null;
        foreach (self::IDS as $id) {
            $item = is_array($items) && is_array($items[$id] ?? null) ? $items[$id] : [];
            $label = ucfirst($id);
            $record = [];
            foreach (['name'=>60, 'tagline'=>120, 'description'=>900, 'audience'=>240, 'price'=>100, 'timeline'=>160, 'features'=>2400, 'button'=>60, 'badge'=>80] as $field => $max) {
                $record[$field] = $read($item, $field, $max, $label . ' ' . $field, $field !== 'badge');
            }
            $features = array_values(array_filter(array_map('trim', explode("\n", $record['features'])), static fn($line) => $line !== ''));
            if (count($features) < 1 || count($features) > 15) $errors[] = $label . ' needs between 1 and 15 features, one per line.';
            $record['features'] = implode("\n", $features);
            $order = $item['order'] ?? null;
            $record['order'] = is_int($order) || is_string($order) ? filter_var($order, FILTER_VALIDATE_INT, ['options'=>['min_range'=>0, 'max_range'=>99]]) : false;
            if ($record['order'] === false) { $errors[] = $label . ' display order must be a whole number from 0 to 99.'; $record['order'] = 0; }
            $enabled = $item['enabled'] ?? '0';
            if (!in_array($enabled, ['0', '1', 0, 1, false, true], true)) $errors[] = $label . ' visibility is invalid.';
            $record['enabled'] = in_array($enabled, ['1', 1, true], true);
            $config['packages'][$id] = $record;
        }
        // The existing settings column is TEXT (64 KiB), so bound the encoded payload too.
        $encoded = json_encode($config, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($encoded === false || strlen($encoded) > 60000) $errors[] = 'Package content is too long to save. Please shorten the descriptions or feature lists.';
        return [$config, array_values(array_unique($errors))];
    }

    public static function save(array $config, ?PDO $pdo = null): void {
        [$config, $errors] = self::validate($config);
        if ($errors) throw new \InvalidArgumentException(implode(' ', $errors));
        $statement = ($pdo ?? Database::pdo())->prepare('INSERT INTO settings(setting_key,setting_value) VALUES(?,?) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)');
        $statement->execute([self::SETTING_KEY, json_encode($config, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)]);
    }

    public static function visible(array $config): array {
        $items = array_filter($config['packages'], static fn($item) => $item['enabled']);
        uasort($items, static fn($a, $b) => $a['order'] <=> $b['order']);
        return $items;
    }

    public static function selected(mixed $id, ?array $config = null): ?array {
        if (!is_string($id) || !in_array($id, self::IDS, true)) return null;
        $item = self::visible($config ?? self::get())[$id] ?? null;
        return $item ? ['id'=>$id, ...$item] : null;
    }

    public static function leadDescription(string $description, array $package): string {
        return "Requested package: " . $package['name'] . " (" . $package['id'] . ")\nDisplayed price: " . $package['price'] . "\n\n" . $description;
    }
}
