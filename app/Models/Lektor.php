<?php

namespace App\Models;

use Core\Database\Database;
use PDO;

class Lektor
{
    public function __construct(
        public readonly Database $database
    )
    {
    }

    public function getAll(): false|array
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
    u.price_per_hour,
    GROUP_CONCAT(DISTINCT t.uuid) AS tag_uuids,
    GROUP_CONCAT(DISTINCT t.name) AS tag_names,
    GROUP_CONCAT(DISTINCT tn.number) AS telephone_numbers,
    GROUP_CONCAT(DISTINCT ea.email) AS email_addresses
FROM
    db.users u
        LEFT JOIN
    db.users_tags ut ON u.uuid = ut.user_uuid
        LEFT JOIN
    db.tags t ON ut.tag_uuid = t.uuid
        LEFT JOIN
    db.telephone_numbers tn ON u.uuid = tn.user_uuid
        LEFT JOIN
    db.email_addresses ea ON u.uuid = ea.user_uuid
GROUP BY
    u.uuid";

        $stmt = $pdo->prepare($query);
        $stmt->execute();

        $lecturers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        // Vytvoření výstupního JSON formátu
        return $this->convertLectors($lecturers);

    }

    private function convertLectors(array $lecturers): array
    {
        $output = [];
        foreach ($lecturers as $lecturer) {

            $tags = [];

            // Kontrola, zda lektor má tagy
            if (!empty($lecturer['tag_uuids'])) {
                $tags = array_map(function ($tagUuid, $tagName) {
                    return ["uuid" => $tagUuid, "name" => $tagName];
                }, explode(',', $lecturer['tag_uuids']), explode(',', $lecturer['tag_names']));
            }

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
                        "telephone_numbers" => $lecturer['telephone_numbers'] ? explode(',', $lecturer['telephone_numbers']) : [],
                        "emails" => $lecturer['email_addresses'] ? explode(',', $lecturer['email_addresses']) : []
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
    u.price_per_hour,
    GROUP_CONCAT(DISTINCT t.uuid) AS tag_uuids,
    GROUP_CONCAT(DISTINCT t.name) AS tag_names,
    GROUP_CONCAT(DISTINCT tn.number) AS telephone_numbers,
    GROUP_CONCAT(DISTINCT ea.email) AS email_addresses
FROM
    db.users u
        LEFT JOIN
    db.users_tags ut ON u.uuid = ut.user_uuid
        LEFT JOIN
    db.tags t ON ut.tag_uuid = t.uuid
        LEFT JOIN
    db.telephone_numbers tn ON u.uuid = tn.user_uuid
        LEFT JOIN
    db.email_addresses ea ON u.uuid = ea.user_uuid
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
        $pdo = $this->database->getPdo();
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

        $stmt = $pdo->prepare($query);
        $stmt->execute([
            ':uuid' => $user_uuid,
            ':title_before' => $data['title_before'],
            ':first_name' => $data['first_name'],
            ':middle_name' => $data['middle_name'],
            ':last_name' => $data['last_name'],
            ':title_after' => $data['title_after'],
            ':picture_url' => $data['picture_url'],
            ':location' => $data['location'],
            ':claim' => $data['claim'],
            ':bio' => $data['bio'],
            ':price_per_hour' => $data['price_per_hour']
        ]);

        // Vložení tagů, kontrola duplicity
        if (isset($data['tags']) && is_array($data['tags'])) {
            foreach ($data['tags'] as $tag) {
                // Zkontrolovat, zda tag s tímto jménem již existuje
                $stmt = $pdo->prepare("SELECT uuid FROM db.tags WHERE name = :name LIMIT 1");
                $stmt->execute([':name' => $tag['name']]);
                $existing_tag_uuid = $stmt->fetchColumn();

                if ($existing_tag_uuid) {
                    // Tag již existuje, použijte existující UUID
                    $tag_uuid = $existing_tag_uuid;
                } else {
                    // Tag neexistuje, vložte nový tag
                    $tag_uuid = $this->database->guidv4();
                    $stmt = $pdo->prepare("INSERT INTO db.tags (uuid, name) VALUES (:uuid, :name)");
                    $stmt->execute([
                        ':uuid' => $tag_uuid,
                        ':name' => $tag['name']
                    ]);
                }

                // Vložení vazby mezi uživatelem a tagem
                $stmt = $pdo->prepare("INSERT INTO db.users_tags (user_uuid, tag_uuid) VALUES (:user_uuid, :tag_uuid)");
                $stmt->execute([':user_uuid' => $user_uuid, ':tag_uuid' => $tag_uuid]);
            }
        }

        // Vložení telefonních čísel
        if (isset($data['contact']['telephone_numbers']) && is_array($data['contact']['telephone_numbers'])) {
            foreach ($data['contact']['telephone_numbers'] as $number) {
                $stmt = $pdo->prepare("INSERT INTO db.telephone_numbers (uuid, user_uuid, number) VALUES (UUID(), :user_uuid, :number)");
                $stmt->execute([':user_uuid' => $user_uuid, ':number' => $number]);
            }
        }

        // Vložení emailových adres
        if (isset($data['contact']['emails']) && is_array($data['contact']['emails'])) {
            foreach ($data['contact']['emails'] as $email) {
                $stmt = $pdo->prepare("INSERT INTO db.email_addresses (uuid, user_uuid, email) VALUES (UUID(), :user_uuid, :email)");
                $stmt->execute([':user_uuid' => $user_uuid, ':email' => $email]);
            }
        }

        return $user_uuid;

    }
}