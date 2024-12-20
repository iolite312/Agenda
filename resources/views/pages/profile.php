<main class="form-signin m-auto vh-100 d-flex flex-column justify-content-center">
    <link rel="stylesheet" href="/assets/css/avatarEditor.css">
    <form action="/profile" method="post" enctype="multipart/form-data"
        class="col-md-4 m-auto d-flex justify-content-center flex-column gap-0">
        <h1 class="h3 mb-3 fw-normal">Edit profile</h1>
        <?php
        if (isset($error)) {
            echo "<div class=\"alert alert-danger\" role=\"alert\">$error</div>";
        }
        ?>
        <div id="avatar-container" class="m-auto">
            <img id="preview"
                src="/assets/images/uploads/<?php echo App\Application\Session::get('user')->profilePicture; ?>"
                alt="Avatar">
        </div>
        <input type="file" id="upload" accept="image/*" class="d-none">
        <button class="btn btn-primary mt-2 m-auto" id="removeButton" type="button" disabled>Remove Avatar</button>

        <div class="modal" tabindex="-1" id="modal">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit profile picture</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <canvas id="modalCanvas" width="400" height="400"></canvas>
                        <div id="slider-container">
                            <label for="slider">Zoom:</label>
                            <input type="range" id="slider" min="0.1" max="3" step="0.1" value="1">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button id="saveButton" type="button" class="btn btn-primary">Save changes</button>
                    </div>
                </div>
            </div>
        </div>

        <div>
            <div class="form-floating my-3">
                <input type="text" class="form-control" id="firstNameInput" placeholder="Jhon" name="firstName"
                    value="<?php echo App\Application\Session::get('user')->firstName; ?>">
                <label for="firstNameInput">Firstname</label>
            </div>
            <div class="form-floating my-3">
                <input type="text" class="form-control" id="lastNameInput" placeholder="Doe" name="lastName"
                    value="<?php echo App\Application\Session::get('user')->lastName; ?>">
                <label for="lastNameInput">Lastname</label>
            </div>
            <div class="form-floating my-3">
                <input type="email" class="form-control" id="floatingInput" placeholder="name@example.com" name="email"
                    value="<?php echo App\Application\Session::get('user')->email; ?>">
                <label for="floatingInput">Email address</label>
            </div>
            <div class="form-floating my-3">
                <input type="password" class="form-control" id="floatingPassword" placeholder="Password" name="password"
                    value="<?php echo $fields['password'] ?? ''; ?>">
                <label for="floatingPassword">Password</label>
            </div>
            <div class="form-floating my-3">
                <input type="password" class="form-control" id="floatingConfirmPassword" placeholder="confirmPassword"
                    name="confirmPassword" value="<?php echo $fields['confirmPassword'] ?? ''; ?>">
                <label for="floatingPassword">Confirm password</label>
            </div>
            <input type="hidden" id="hiddenAvatar" name="avatarData" value="">
            <button class="btn btn-primary w-100 py-2" type="submit">Save Changes</button>
        </div>
    </form>
</main>
<script src="/assets/js/avatarEditor.js"></script>