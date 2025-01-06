<?php

namespace App\Models;

class Appointments
{
    public int $id;
    public \DateTime $start_time;
    public \DateTime $end_time;
    public string $name;
    public ?string $description;
    public ?string $color;
    public int $agenda_id;

    public function __construct(
        int $id,
        \DateTime $start_time,
        \DateTime $end_time,
        string $name,
        ?string $description,
        ?string $color,
        int $agenda_id,
    ) {
        $this->id = $id;
        $this->start_time = $start_time;
        $this->end_time = $end_time;
        $this->name = $name;
        $this->description = $description;
        $this->color = $color;
        $this->agenda_id = $agenda_id;
    }

    public static function fromDatabase(array $appointment): self
    {
        return new self(
            (int) $appointment['id'],
            new \DateTime($appointment['start_time']),
            new \DateTime($appointment['end_time']),
            $appointment['name'],
            $appointment['description'] ?? null,
            $appointment['color'] ?? null,
            (int) $appointment['agenda_id']
        );
    }
}
