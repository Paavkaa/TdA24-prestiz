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

    public function getAll(): false|array
    {

        $query = "SELECT
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
    u.price_per_hour
FROM
    db.users u
GROUP BY
    u.uuid";

        $stmt = $this->pdo->prepare($query);
        $stmt->execute();

        $lecturers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        // Vytvoření výstupního JSON formátu
        return $this->convertLectors($lecturers);

    }

    private function convertLectors(array $lecturers): array
    {
        $output = [];
        foreach ($lecturers as $lecturer) {

            $uuid = $lecturer['uuid'];
            $query = "SELECT tn.number
        FROM db.telephone_numbers tn
        WHERE tn.user_uuid = :uuid
        ORDER BY tn.position";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute(['uuid' => $uuid]);
            $phoneNumbers = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $phoneNumbers = array_map(function ($number) {
                return $number['number'];
            }, $phoneNumbers);
            $query = "SELECT t.uuid,t.name
        FROM db.tags t
        LEFT JOIN db.users_tags ut ON t.uuid = ut.tag_uuid
        WHERE ut.user_uuid = :uuid";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute(['uuid' => $uuid]);
            $tags = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $query = "SELECT ea.email
        FROM db.email_addresses ea
        WHERE ea.user_uuid = :uuid
        order by ea.position";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute(['uuid' => $uuid]);
            $emails = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $emails = array_map(function ($email) {
                return $email['email'];
            }, $emails);

            $output[] =
                [
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
                        "telephone_numbers" => $phoneNumbers,
                        "emails" => $emails
                    ]
                ];
        }
        return $output;
    }

    public function getById(string $uuid): false|array
    {
        $pdo = $this->database->getPdo();

        $query = "SELECT
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
    u.price_per_hour
FROM
    db.users u
WHERE u.uuid = :uuid
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
        if (!$this->lecturerExit($uuid)) {
            return false;
        }
        // Aktualizace záznamu v tabulce 'users'
        $query = "UPDATE db.users
                  SET
                    title_before = COALESCE(:title_before, title_before),
                    first_name = COALESCE(:first_name, first_name),
                    middle_name = COALESCE(:middle_name, middle_name),
                    last_name = COALESCE(:last_name, last_name),
                    title_after = COALESCE(:title_after, title_after),
                    picture_url = COALESCE(:picture_url, picture_url),
                    location = COALESCE(:location, location),
                    claim = COALESCE(:claim, claim),
                    bio = COALESCE(:bio, bio),
                    price_per_hour = COALESCE(:price_per_hour, price_per_hour)
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

        // Smazání existujících vazeb mezi lektorem a tagy
        $stmt = $this->pdo->prepare("DELETE FROM db.users_tags WHERE user_uuid = :uuid");
        $stmt->execute([':uuid' => $uuid]);

        // Vložení nových vazeb mezi lektorem a tagy (zůstává beze změn)
        if (isset($data['tags']) && is_array($data['tags'])) {
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

        // Smazání existujících telefonních čísel a emailových adres
        $stmt = $this->pdo->prepare("DELETE FROM db.telephone_numbers WHERE user_uuid = :uuid");
        $stmt->execute([':uuid' => $uuid]);

        $stmt = $this->pdo->prepare("DELETE FROM db.email_addresses WHERE user_uuid = :uuid");
        $stmt->execute([':uuid' => $uuid]);

        // Vložení nových telefonních čísel (zůstává beze změn)
        $this->extracted($uuid, $data);

        return true;
    }

    private function lecturerExit(string $uuid): bool
    {
        $pdo = $this->database->getPdo();
        $query = "SELECT * FROM db.users WHERE uuid = :uuid";
        $stmt = $pdo->prepare($query);
        $stmt->execute(['uuid' => $uuid]);
        return count($stmt->fetchAll(PDO::FETCH_ASSOC)) > 0;
    }

    public function lecturerDelete(string $uuid): bool
    {
        if (!$this->lecturerExit($uuid)) {
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