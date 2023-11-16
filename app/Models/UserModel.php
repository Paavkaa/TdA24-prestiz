<?php
namespace App\Models;

class UserModel {
    public function getAllUsers():array
    {
        // Logika pro získání všech uživatelů z databáze
        return [
            [
                'id' => 1,
                'name' => 'John Doe',
                'email' => 'john.doe@gmail.com'
            ],
        ];
    }

    // Další metody pro manipulaci s daty uživatelů...
}
