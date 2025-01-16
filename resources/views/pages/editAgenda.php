<main class="m-auto vh-100 d-flex flex-column justify-content-center">
    <link rel="stylesheet" href="/assets/css/editAgenda.css">
    <div class="col-md-12 col-lg-9 col-xl-7 m-auto">
        <h2>Edit agenda</h2>
        <h4>Change name and description</h4>
        <form action="/agenda/<?php echo $id; ?>/edit" method="post" class="mb-3">
            <div>
                <div class="form-floating my-3">
                    <input type="text" class="form-control" id="agendaNameInput" placeholder="agenda" name="agendaName"
                        value="<?php foreach ($agendas as $agenda) {
                            if ($agenda->id == $id) {
                                echo trim($agenda->name);
                            }
                        } ?>">
                    <label for="agendaNameInput">Agenda name</label>
                </div>
                <div class="form-floating my-3">
                    <input type="text" class="form-control" id="agendaDescriptionInput" placeholder="Doe"
                        name="agendaDescription" value="<?php foreach ($agendas as $agenda) {
                            if ($agenda->id == $id) {
                                echo trim($agenda->description);
                            }
                        } ?>">
                    <label for="agendaDescriptionInput">Agenda description</label>
                </div>
                <button class="btn btn-primary w-100 py-2" type="submit">Save Changes</button>
            </div>
        </form>
        <h4>Change permissions</h4>
        <div class="alert alert-success" id="successAlert" style="display: none;" role="alert"></div>
        <div class="alert alert-danger" id="errorAlert" style="display: none;" role="alert"></div>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">User</th>
                    <th scope="col">Permission</th>
                    <th scope="col">Invitation</th>
                    <th scope="col" class="min-width">Edit</th>
                </tr>
            </thead>
            <tbody id="userTable">
            </tbody>
        </table>
        <button type="button" class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#addUserModal">Add
            user</button>
        <div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add user to agenda</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="" method="post">
                        <div class="modal-body">
                            <div class="input-group mb-3">
                                <span class="input-group-text" id="emailLabel">Users email</span>
                                <input type="email" id="userEmailInput" class="form-control" placeholder="email"
                                    aria-label="email" aria-describedby="emailLabel">
                            </div>

                            <div class="input-group mb-3">
                                <label class="input-group-text" for="roleSelector">Roles</label>
                                <select class="form-select" id="roleSelector">
                                    <option selected>Choose...</option>
                                    <option value="user">User</option>
                                    <option value="guest">Guest</option>
                                </select>
                            </div>

                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-primary" onclick="addUser()">Add user</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>
<script>
    let usersWithAccess;
    const userTable = document.getElementById("userTable");
    const addUserModal = document.getElementById("addUserModal");
    const successAlert = document.getElementById("successAlert");
    const errorAlert = document.getElementById("errorAlert");

    function addUser() {
        errorAlert.style.display = "none";
        successAlert.style.display = "none";
        const email = document.getElementById("userEmailInput").value;
        const permission = document.getElementById("roleSelector").value;
        const user = {
            email: email,
            permission: permission
        };
        fetch("/agenda/" + <?php echo $id; ?> + "/edit/adduser", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify(user)
        }).then(response => response.json()).then(data => {
            console.log(data);
            if (data.status === "success") {
                usersWithAccess.push(data.data);
                createTable();
                successAlert.style.display = "block";
                successAlert.textContent = data.message;
            } else {
                errorAlert.style.display = "block";
                errorAlert.textContent = data.message;
            }
            let modalInstance = bootstrap.Modal.getInstance(addUserModal);
            if (!modalInstance) {
                modalInstance = new bootstrap.Modal(modal);
            }
            modalInstance.hide();
        })
    }

    function getUsers() {
        fetch("/agenda/" + <?php echo $id; ?> + "/edit/users").then(response => response.json()).then(data => {
            if (data.status === "success") {
                usersWithAccess = data.data;
                createTable();
            }
        })
    }
    function removeUser(email) {
        fetch("/agenda/" + <?php echo $id; ?> + "/edit/removeuser", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                email: email
            })
        }).then(response => response.json()).then(data => {
            if (data.status === "success") {
                usersWithAccess = usersWithAccess.filter(user => user.email !== email);
                successAlert.style.display = "block";
                successAlert.textContent = data.message;
                createTable();
            } else {
                errorAlert.style.display = "block";
                errorAlert.textContent = data.message;
            }
        })
    }

    function createTable() {
        userTable.innerHTML = "";
        for (let i = 0; i < usersWithAccess.length; i++) {
            const user = usersWithAccess[i];
            const row = document.createElement("tr");
            row.innerHTML = `<th scope="row">${i + 1}</th>
                                    <td>${user.first_name} ${user.last_name} (${user.email})</td>
                                    <td>${user.role}</td>
                                    <td>${user.accepted}</td>`;
            if (user.role != "admin") {
                row.innerHTML += `<td class="min-width">
                                    <button type="button" class="btn btn-primary mx-2"><img src="/assets/images/edit.svg" alt="trashcan"></button>
                                    <button type="button" class="btn btn-danger"><img src="/assets/images/trashcan.svg" onclick="removeUser('${user.email}')" alt="trashcan"></button>
                                </td>`
            } else {
                row.innerHTML += `<td class="min-width">
                                    <button type="button" class="btn btn-primary mx-2" disabled><img src="/assets/images/edit.svg" alt="trashcan"></button>
                                    <button type="button" class="btn btn-danger" disabled><img src="/assets/images/trashcan.svg" alt="trashcan"></button>
                                </td>`
            }
            userTable.appendChild(row);
        }
    }
    getUsers();
</script>