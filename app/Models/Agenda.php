<?php

namespace App\Models;

class Agenda
{
    public int $id;
    public string $name;
    public ?string $description;
    public string $default_color;
    public bool $personal_agenda;

    public function __construct($id, $name, $description, $default_color, $personal_agenda)
    {
        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
        $this->default_color = $default_color;
        $this->personal_agenda = (bool) $personal_agenda;
    }

    public static function fromDatabase(array $agenda): self
    {
        return new self(
            $agenda['id'],
            $agenda['name'],
            $agenda['description'],
            $agenda['default_color'],
            $agenda['personal_agenda']
        );
    }
}
