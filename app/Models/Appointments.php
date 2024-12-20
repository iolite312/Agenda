<?php

namespace App\Models;

class Appointments
{
    public int $id;
    public \DateTime $start_time;
    public \DateTime $end_time;
    public string $name;
    public string $description;
    public string $color;
    public int $agenda_id;
}
