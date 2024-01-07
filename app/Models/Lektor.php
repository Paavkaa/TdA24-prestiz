<?php

namespace App\Models;

use Core\Database\Database;
use PDO;

class Lektor
{
    private readonly PDO $pdo;

    public function __construct(
        public readonly Database $database
    )
    {
        $this->pdo = $database->getPdo();
    }

    public function getAll(?array $where = null): false|array
    {
        $whereClauses = [];
        $bindings = [];

        // Přidání podmínek pro běžné sloupce
        if ($where !== null) {
            foreach ($where as $key => $value) {
                if ($key === 'tags' || $key === 'contact' || $key === 'emails' || $key === 'telephone_numbers') {
                    continue;
                }
                $whereClauses[] = "u.{$key} LIKE :{$key}";
                $bindings[$key] = "%{$value}%";
            }
        }

        // Přidání podmínek pro tagy
        $tagsJoin = '';
        if (isset($where['tags']) && is_array($where['tags'])) {
            $uniqueTags = array_unique($where['tags']);
            $tagsCount = count($uniqueTags);
            $tagsJoin = "
            JOIN (
                SELECT ut.user_uuid
                FROM db.users_tags ut
                WHERE ut.tag_uuid IN (" . implode(',', array_fill(0, $tagsCount, '?')) . ")
                GROUP BY ut.user_uuid
                HAVING COUNT(DISTINCT ut.tag_uuid) = $tagsCount
            ) AS tags_join ON u.uuid = tags_join.user_uuid
        ";
            $bindings = array_merge($bindings, $uniqueTags);
        }

        $whereSQL = $whereClauses ? 'WHERE ' . implode(' AND ', $whereClauses) : '';

        // Hlavní dotaz s JOINy pro telefonní čísla, emaily a tagy
        $query = "
        SELECT
            u.uuid,
            u.title_before,
            u.first_name,
            u.middle_name,
            u.last_name,
            u.title_after,
            u.picture_url,
            u.location,
            u.claim,
            u.bio,
            u.price_per_hour,
            GROUP_CONCAT(DISTINCT tn.number) AS telephone_numbers,
            GROUP_CONCAT(DISTINCT ea.email) AS emails,
            GROUP_CONCAT(DISTINCT CONCAT_WS(':', t.uuid, t.name)) AS tags
        FROM
            db.users u
            LEFT JOIN db.telephone_numbers tn ON u.uuid = tn.user_uuid
            LEFT JOIN db.email_addresses ea ON u.uuid = ea.user_uuid
            LEFT JOIN db.users_tags ut ON u.uuid = ut.user_uuid
            LEFT JOIN db.tags t ON ut.tag_uuid = t.uuid
        $tagsJoin
        $whereSQL
        GROUP BY
            u.uuid
    ";
        $stmt = $this->pdo->prepare($query);

        // Bind the parameters
        foreach ($bindings as $key => $value) {
            if (is_int($key)) {
                $stmt->bindValue($key + 1, $value);
            } else {
                $stmt->bindValue(":{$key}", $value);
            }
        }

        $stmt->execute();

        $lecturers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $this->convertLectors($lecturers);
    }

    private function convertLectors(array $lecturers): array
    {
        return array_map(function ($lecturer) {
            // Rozdělení tagů a jejich uuid
            $tags = array_map(function ($tag) {
                [$uuid, $name] = explode(':', $tag);
                return ['uuid' => $uuid, 'name' => $name];
            }, explode(',', $lecturer['tags']));

            return [
                "uuid" => $lecturer['uuid'],
                "title_before" => $lecturer['title_before'],
                "first_name" => $lecturer['first_name'],
                "middle_name" => $lecturer['middle_name'],
                "last_name" => $lecturer['last_name'],
                "title_after" => $lecturer['title_after'],
                "picture_url" => $lecturer['picture_url'],
                "location" => $lecturer['location'],
                "claim" => $lecturer['claim'],
                "bio" => $lecturer['bio'],
                "tags" => $tags,
                "price_per_hour" => (int)$lecturer['price_per_hour'],
                "contact" => [
                    "telephone_numbers" => explode(',', $lecturer['telephone_numbers']),
                    "emails" => explode(',', $lecturer['emails']),
                ]
            ];
        }, $lecturers);
    }

    public function createLector(array $data): bool|string
    {
        $user_uuid = $this->database->guidv4();
        // Vložení nového uživatele do tabulky 'users'
        $query = "INSERT INTO db.users (
                    uuid,
                    title_before,
                    first_name,
                    middle_name,
                    last_name,
                    title_after,
                    picture_url,
                    location,
                    claim,
                    bio,
                    price_per_hour
                ) VALUES (
                    :uuid,
                    :title_before,
                    :first_name,
                    :middle_name,
                    :last_name,
                    :title_after,
                    :picture_url,
                    :location,
                    :claim,
                    :bio,
                    :price_per_hour
                )";

        $stmt = $this->pdo->prepare($query);
        $stmt->execute([
            ':uuid' => $user_uuid,
            ':title_before' => $data['title_before'] ?? null,
            ':first_name' => $data['first_name'] ?? null,
            ':middle_name' => $data['middle_name'] ?? null,
            ':last_name' => $data['last_name'] ?? null,
            ':title_after' => $data['title_after'] ?? null,
            ':picture_url' => $data['picture_url'] ?? null,
            ':location' => $data['location'] ?? null,
            ':claim' => $data['claim'] ?? null,
            ':bio' => $data['bio'] ?? null,
            ':price_per_hour' => $data['price_per_hour'] ?? null
        ]);

        // Vložení tagů, kontrola duplicity
        if (isset($data['tags']) && is_array($data['tags'])) {
            foreach ($data['tags'] as $tag) {
                // Zkontrolovat, zda tag s tímto jménem již existuje
                $stmt = $this->pdo->prepare("SELECT uuid FROM db.tags WHERE name = :name LIMIT 1");
                $stmt->execute([':name' => $tag['name']]);
                $existing_tag_uuid = $stmt->fetchColumn();

                if ($existing_tag_uuid) {
                    // Tag již existuje, použijte existující UUID
                    $tag_uuid = $existing_tag_uuid;
                } else {
                    // Tag neexistuje, vložte nový tag
                    $tag_uuid = $this->database->guidv4();
                    $stmt = $this->pdo->prepare("INSERT INTO db.tags (uuid, name) VALUES (:uuid, :name)");
                    $stmt->execute([
                        ':uuid' => $tag_uuid,
                        ':name' => $tag['name']
                    ]);
                }

                // Vložení vazby mezi uživatelem a tagem
                $stmt = $this->pdo->prepare("INSERT INTO db.users_tags (user_uuid, tag_uuid) VALUES (:user_uuid, :tag_uuid)");
                $stmt->execute([':user_uuid' => $user_uuid, ':tag_uuid' => $tag_uuid]);
            }
        }

        // Vložení telefonních čísel
        $this->extracted($user_uuid, $data);

        return $user_uuid;

    }

    /**
     * @param string $uuid
     * @param array $data
     * @return void
     */
    private function extracted(string $uuid, array $data): void
    {
        if (isset($data['contact']['telephone_numbers']) && is_array($data['contact']['telephone_numbers'])) {
            foreach ($data['contact']['telephone_numbers'] as $key => $number) {
                $stmt = $this->pdo->prepare("INSERT INTO db.telephone_numbers (uuid, user_uuid, number,position) VALUES (UUID(), :user_uuid, :number,:position)");
                $stmt->execute([':user_uuid' => $uuid, ':number' => $number, ':position' => $key]);
            }
        }

        // Vložení nových emailových adres (zůstává beze změn)
        if (isset($data['contact']['emails']) && is_array($data['contact']['emails'])) {
            foreach ($data['contact']['emails'] as $key => $email) {
                $stmt = $this->pdo->prepare("INSERT INTO db.email_addresses (uuid, user_uuid, email,position) VALUES (UUID(), :user_uuid, :email,:position)");
                $stmt->execute([':user_uuid' => $uuid, ':email' => $email, ':position' => $key]);
            }
        }
    }

    public function updateLecturer(string $uuid, array $data): bool
    {
        if (!$this->lecturerExists($uuid)) {
            return false;
        }
        $lecturerData = $this->getById($uuid);
        foreach ($lecturerData as $key => $value) {
            if ($key === 'contact' || $key === 'tags') {
                continue;
            }
            // Pokud klíč neexistuje v poli uživatelského vstupu, přidáme hodnotu z databáze
            if (!array_key_exists($key, $data)) {
                $data[$key] = $value;
            } else {
                // Pokud klíč existuje, ale hodnota je NULL, necháme ji tak
                if ($data[$key] === null) {
                    $data[$key] = null;
                }
                // Jinak uživatelský vstup má přednost a ponecháme hodnotu, kterou uživatel zadal
            }
        }
        // Aktualizace záznamu v tabulce 'users'
        $query = "UPDATE db.users
                  SET
                    title_before = :title_before,
                    first_name = :first_name,
                    middle_name = :middle_name,
                    last_name = :last_name,
                    title_after = :title_after,
                    picture_url = :picture_url,
                    location = :location,
                    claim = :claim,
                    bio = :bio,
                    price_per_hour = :price_per_hour
                  WHERE
                    uuid = :uuid";

        $stmt = $this->pdo->prepare($query);
        $stmt->execute([
            ':title_before' => $data['title_before'] ?? null,
            ':first_name' => $data['first_name'] ?? null,
            ':middle_name' => $data['middle_name'] ?? null,
            ':last_name' => $data['last_name'] ?? null,
            ':title_after' => $data['title_after'] ?? null,
            ':picture_url' => $data['picture_url'] ?? null,
            ':location' => $data['location'] ?? null,
            ':claim' => $data['claim'] ?? null,
            ':bio' => $data['bio'] ?? null,
            ':price_per_hour' => $data['price_per_hour'] ?? null,
            ':uuid' => $uuid
        ]);

        // Vložení nových vazeb mezi lektorem a tagy (zůstává beze změn)
        if (isset($data['tags']) && is_array($data['tags'])) {
            // Smazání existujících vazeb mezi lektorem a tagy
            $stmt = $this->pdo->prepare("DELETE FROM db.users_tags WHERE user_uuid = :uuid");
            $stmt->execute([':uuid' => $uuid]);

            foreach ($data['tags'] as $tag) {
                $stmt = $this->pdo->prepare("INSERT IGNORE INTO db.tags (uuid, name) VALUES (UUID(), :name)");
                $stmt->execute([':name' => $tag['name']]);

                $stmt = $this->pdo->prepare("SELECT uuid FROM db.tags WHERE name = :name LIMIT 1");
                $stmt->execute([':name' => $tag['name']]);
                $tag_uuid = $stmt->fetchColumn();

                $stmt = $this->pdo->prepare("INSERT INTO db.users_tags (user_uuid, tag_uuid) VALUES (:user_uuid, :tag_uuid)");
                $stmt->execute([':user_uuid' => $uuid, ':tag_uuid' => $tag_uuid]);
            }
        }

        if (isset($data['contact']['telephone_numbers']) && is_array($data['contact']['telephone_numbers'])) {
            // Smazání existujících telefonních čísel
            $stmt = $this->pdo->prepare("DELETE FROM db.telephone_numbers WHERE user_uuid = :uuid");
            $stmt->execute([':uuid' => $uuid]);
        }
        if (isset($data['contact']['emails']) && is_array($data['contact']['emails'])) {
            // Smazání existujících emailových adres
            $stmt = $this->pdo->prepare("DELETE FROM db.email_addresses WHERE user_uuid = :uuid");
            $stmt->execute([':uuid' => $uuid]);
        }
        // Vložení nových telefonních čísel (zůstává beze změn)
        $this->extracted($uuid, $data);

        return true;
    }

    private function lecturerExists(string $uuid): bool
    {
        $query = "SELECT COUNT(*) FROM db.users WHERE uuid = :uuid";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['uuid' => $uuid]);
        return $stmt->fetchColumn() > 0;
    }

    public function getById(string $uuid): false|array
    {
        $pdo = $this->database->getPdo();


        $query = "
        SELECT
            u.uuid,
            u.title_before,
            u.first_name,
            u.middle_name,
            u.last_name,
            u.title_after,
            u.picture_url,
            u.location,
            u.claim,
            u.bio,
            u.price_per_hour,
            GROUP_CONCAT(DISTINCT tn.number) AS telephone_numbers,
            GROUP_CONCAT(DISTINCT ea.email) AS emails,
            GROUP_CONCAT(DISTINCT CONCAT_WS(':', t.uuid, t.name)) AS tags
        FROM
            db.users u
            LEFT JOIN db.telephone_numbers tn ON u.uuid = tn.user_uuid
            LEFT JOIN db.email_addresses ea ON u.uuid = ea.user_uuid
            LEFT JOIN db.users_tags ut ON u.uuid = ut.user_uuid
            LEFT JOIN db.tags t ON ut.tag_uuid = t.uuid
        WHERE
            u.uuid = :uuid
        GROUP BY
            u.uuid
    ";
        $stmt = $pdo->prepare($query);
        $stmt->execute(['uuid' => $uuid]);

        $lecturers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        // Vytvoření výstupního JSON formátu
        $lector = $this->convertLectors($lecturers);
        if (count($lector) > 0) {
            return $lector[0];
        } else {
            return false;
        }
    }

    public function lecturerDelete(string $uuid): bool
    {
        if (!$this->lecturerExists($uuid)) {
            return false;
        }
        $pdo = $this->database->getPdo();
        // Smazání vazeb mezi lektorem a tagy
        $stmt = $pdo->prepare("DELETE FROM db.users_tags WHERE user_uuid = :uuid");
        $stmt->execute([':uuid' => $uuid]);

        // Smazání telefonních čísel
        $stmt = $pdo->prepare("DELETE FROM db.telephone_numbers WHERE user_uuid = :uuid");
        $stmt->execute([':uuid' => $uuid]);

        // Smazání emailových adres
        $stmt = $pdo->prepare("DELETE FROM db.email_addresses WHERE user_uuid = :uuid");
        $stmt->execute([':uuid' => $uuid]);
        // Smazání záznamu z tabulky 'users'
        $stmt = $pdo->prepare("DELETE FROM db.users WHERE uuid = :uuid");
        $stmt->execute([':uuid' => $uuid]);


        return true;
    }
}