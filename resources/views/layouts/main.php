<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agenda - <?php echo $page; ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/sidebar.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Exo+2&display=swap" rel="stylesheet">
</head>

<body class="container-fluid">
    <div class="row">
        <div class="d-flex flex-column flex-shrink-0 p-3 col-md-3 col-lg-2 bg-body-tertiary vh-100 position-fixed">
            <a href="/"
                class="d-flex align-items-center mb-3 mb-md-0 me-md-auto link-body-emphasis text-decoration-none">
                <span class="fs-4">Agenda</span>
            </a>
            <hr>
            <ul class="nav nav-pills flex-column mb-auto">
                <li class="nav-item">
                    <a href="/" class="nav-link <?php echo $page == 'home' ? 'active' : ''; ?> link-light"
                        aria-current="page">
                        Home
                    </a>
                </li>
                <?php
                foreach ($agendas as $agenda) {
                    if (!$agenda->personal_agenda) {
                        continue;
                    }
                    App\Application\Session::set('personal_agenda', $agenda->id);
                    echo '<li class="nav-item">
                            <a href="/agenda/' . $agenda->id . '" class="nav-link ' . ($page == 'agenda' && $id == $agenda->id ? 'active' : '') . ' link-light">
                                Personal Agenda
                            </a>
                        </li>';
                }
                ?>
                <hr>
                <?php
                foreach ($agendas as $agenda) {
                    if ($agenda->personal_agenda) {
                        continue;
                    }
                    $roles = App\Application\Session::get('user_roles');
                    $userRole = array_filter($roles, fn($role) => array_key_exists($agenda->id, $role));
                    $role = array_values($userRole)[0][$agenda->id];
                    echo '<div class="' . ($role == App\Enums\AgendaRolesEnum::ADMIN ? 'btn-group ' : '') . 'w-100">
                            <a href="/agenda/' . $agenda->id . '" class="nav-link ' . (($page == 'agenda' || $page == 'edit') && $id == $agenda->id ? 'active' : '') . ' link-light flex-grow-1' . ($role == App\Enums\AgendaRolesEnum::ADMIN ? ' rounded-end-0' : '') . '" title="' . $agenda->name . ' - ' . $agenda->description . '">
                                    ' . $agenda->name . '
                                </a>
                            <button type="button"
                                class="btn ' . (($page == 'agenda' || $page == 'edit') && $id == $agenda->id ? 'btn-primary' : '') . ' dropdown-toggle dropdown-toggle-split resize-button' . ($role == App\Enums\AgendaRolesEnum::USER ? ' d-none' : '') . '"
                                data-bs-toggle="dropdown" aria-expanded="false" data-bs-reference="parent">
                                <span class="visually-hidden">Toggle Dropdown</span>
                            </button>
                            <ul class="dropdown-menu ' . ($role == App\Enums\AgendaRolesEnum::USER ? 'd-none' : '') . '">
                                <li><a class="dropdown-item" href="/agenda/' . $agenda->id . '/edit">Edit agenda</a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><button class="dropdown-item" onclick="deleteAgenda(' . $agenda->id . ')" href="#" data-bs-toggle="modal" data-bs-target="#deleteAgendaModal">Delete agenda</button></li>
                            </ul>
                        </div>';
                }
                ?>
            </ul>
            <hr>
            <ul class="nav nav-pills flex-column">
                <li class="nav-item">
                    <button class="nav-link link-light" data-bs-toggle="modal" data-bs-target="#newAgendaModal">
                        Create New Agenda
                    </button>
                </li>
            </ul>
            <hr>
            <div class="dropdown">
                <a href="#" class="d-flex align-items-center link-body-emphasis text-decoration-none dropdown-toggle"
                    data-bs-toggle="dropdown" aria-expanded="false">
                    <img src="/assets/images/uploads/<?php echo App\Application\Session::get('user')->profilePicture; ?>"
                        alt="" width="32" height="32" class="rounded-circle me-2">
                    <strong><?php echo App\Application\Session::get('user')->fullName; ?></strong>
                </a>
                <ul class="dropdown-menu text-small shadow">
                    <li><a class="dropdown-item" href="/profile">Profile settings</a></li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li><a class="dropdown-item" href="/logout">Sign out</a></li>
                </ul>
            </div>
        </div>
        <div class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            {{content}}
        </div>
    </div>
    <div class="modal fade" id="newAgendaModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create new agenda</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="/agenda/create" method="post">
                    <div class="modal-body">
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" id="floatingInput" name="agendaName"
                                placeholder="Agenda name">
                            <label for="floatingInput">Agenda Name</label>
                        </div>
                        <div class="form-floating">
                            <input type="text" class="form-control" id="floatingAgendaDescription"
                                name="agendaDescription" placeholder="Agenda description">
                            <label for="floatingAgendaDescription">Agenda Description</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Create agenda</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal fade" id="deleteAgendaModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delete agenda</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="" method="post">
                    <div class="modal-body">
                        <h3>Are you sure you want to delete this agenda?</h3>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete agenda</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>

<script>
    function deleteAgenda(id) {
        modal = document.getElementById('deleteAgendaModal');
        modal.querySelector('form').action = '/agenda/' + id + '/delete';
    }
</script>

</html>