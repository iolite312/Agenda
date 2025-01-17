<div class="mt-3">
    <div class="d-flex justify-content-between">
        <h2>Agenda</h2>
        <div class="d-flex align-items-center gap-2">
            <div class="d-flex align-items-center">
                <button class="btn btn-secondary d-flex align-items-center" id="prevWeek"><img
                        src="/assets/images/left.svg" alt="left arrow"></button>
                <div class="mx-2" id="weekDisplay"></div>
                <button class="btn btn-secondary d-flex align-items-center" id="nextWeek"><img
                        src="/assets/images/right.svg" alt="right arrow"></button>
            </div>
            <?php
            $roles = App\Application\Session::get('user_roles');
            $userRole = array_filter($roles, fn($role) => array_key_exists($id, $role));
            $role = array_values($userRole)[0][$id];
            echo "<button class='btn btn-primary " . ($role == App\Enums\AgendaRolesEnum::GUEST ? 'd-none ' : 'd-flex ') . " align-items-center' data-bs-toggle='modal'
                data-bs-target='#addAppointmentModal'><img src='/assets/images/plus.svg' alt='plus'></button>"
                ?>
        </div>
    </div>
    <div class="alert alert-success" id="successAlert" style="display: none;" role="alert"></div>
    <div class="alert alert-danger" id="errorAlert" style="display: none;" role="alert"></div>
    <div id="calendar">
    </div>
</div>
<div class="modal fade" id="addAppointmentModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add appointment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="" method="post">
                <div class="modal-body">
                    <div class="input-group mb-3">
                        <span class="input-group-text" id="nameLabel">Name</span>
                        <input type="text" id="appointmentNameInput" class="form-control"
                            placeholder="Appointment name">
                    </div>
                    <div class="input-group mb-3">
                        <span class="input-group-text" id="descriptionLabel">Description</span>
                        <input type="text" id="appointmentDescriptionInput" class="form-control"
                            placeholder="Appointment description">
                    </div>
                    <div class="input-group mb-3">
                        <span class="input-group-text" id="starttimeLabel">Start time</span>
                        <input type="datetime-local" id="startTimeInput" value="2025-01-01T00:00" class="form-control"
                            placeholder="Appointment start_time">
                    </div>
                    <div class="input-group mb-3">
                        <span class="input-group-text" id="endtimeLabel">End time</span>
                        <input type="datetime-local" id="endTimeInput" value="2025-01-01T00:00" class="form-control"
                            placeholder="Appointment end_time">
                    </div>
                    <div class="input-group mb-3">
                        <span class="input-group-text" id="colorLabel">Color</span>
                        <input type="color" id="appointmentColorInput" class="form-control"
                            placeholder="Appointment description" style="height: 38px;" value="<?php
                            foreach ($agendas as $key => $value) {
                                if ($value->id == App\Application\Request::getParam('id')) {
                                    echo $value->default_color;
                                }
                            }
                            ?>">
                    </div>

                </div>
                <div class="modal-footer">
                    <button id="deleteButton" type="button" class="btn btn-danger" style="display: none;"
                        data-bs-dismiss="modal" onclick="removeAppointment()">Delete
                        appointment</button>
                    <div class="d-flex gap-1">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button id="addButton" type="button" class="btn btn-primary" onclick="addAppointment()">Add
                            appointment</button>
                        <button id="editButton" type="button" class="btn btn-primary" style="display: none;"
                            onclick="editAppointment()">Update
                            appointment</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    const id = "<?php echo App\Application\Request::getParam('id'); ?>";
    const invitationStatus = "<?php echo $inviteStatus ?>";
    if (invitationStatus != null && invitationStatus == "pending") {
        if (confirm("You have been invited to this agenda. Do you want to join?")) {
            fetch("/agenda/" + id + "/invitation", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({ status: "accepted" })
            });
        } else {
            fetch("/agenda/" + id + "/invitation", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({ status: "declined" })
            });
            setTimeout(() => {
                window.location.href = "/";
            }, 300)
        }
    }
</script>
<script>
    const searchParams = new URLSearchParams(window.location.search);
    const weekDisplay = document.getElementById('weekDisplay');
    const successAlert = document.getElementById("successAlert");
    const errorAlert = document.getElementById("errorAlert");
    const editButton = document.getElementById('editButton');
    const deleteButton = document.getElementById('deleteButton');
    const addButton = document.getElementById('addButton');
    const ws = new WebSocket('ws://<?php echo $_SERVER['HTTP_HOST']; ?>:8082');

    // Listen for connection open
    ws.onopen = () => {
        console.log('Connected to WebSocket server.');
        ws.send(JSON.stringify({ action: 'join', room: <?php echo App\Application\Request::getParam('id'); ?> }));
        ws.send(JSON.stringify({ action: 'appointments', id: <?php echo App\Application\Request::getParam('id'); ?>, week: searchParams.get('week'), year: searchParams.get('year') }));
    };

    ws.onmessage = (event) => {
        const response = JSON.parse(event.data);
        switch (response.trigger) {
            case 'appointments':
                getAppointments(response.appointments);
                break;
            case 'update':
                ws.send(JSON.stringify({ action: 'appointments', id: <?php echo App\Application\Request::getParam('id'); ?>, week: searchParams.get('week'), year: searchParams.get('year') }));
                getAppointments(response.appointments);
                break;
            case 'make-appointment':
                if (response.status == 'success') {
                    successAlert.style.display = "block";
                    successAlert.textContent = "Appointment created successfully";
                    setTimeout(() => {
                        successAlert.style.display = "none";
                    }, 5000);
                } else {
                    errorAlert.style.display = "block";
                    errorAlert.textContent = response.message ?? "Error creating appointment";
                    setTimeout(() => {
                        errorAlert.style.display = "none";
                    }, 5000);
                }
                break;
            case 'update-appointment':
                if (response.status == 'success') {
                    successAlert.style.display = "block";
                    successAlert.textContent = "Appointment updated successfully";
                    setTimeout(() => {
                        successAlert.style.display = "none";
                    }, 5000);
                } else {
                    errorAlert.style.display = "block";
                    errorAlert.textContent = "Error updating appointment";
                    setTimeout(() => {
                        errorAlert.style.display = "none";
                    }, 5000);
                }
                break;
            case 'remove-appointment':
                if (response.status == 'success') {
                    successAlert.style.display = "block";
                    successAlert.textContent = "Appointment deleted successfully";
                    setTimeout(() => {
                        successAlert.style.display = "none";
                    }, 5000);
                } else {
                    errorAlert.style.display = "block";
                    errorAlert.textContent = "Error deleting appointment";
                    setTimeout(() => {
                        errorAlert.style.display = "none";
                    }, 5000);
                }
                break;
            default:
                console.log(response);
                break;
        }
    };

    // Listen for errors
    ws.onerror = (error) => {
        console.error('WebSocket error:', error);
        errorAlert.style.display = "block";
        errorAlert.textContent = "Please ensure that Ratchet is running or try refreshing the page";
    };

    // Listen for connection close
    ws.onclose = () => {
        console.log('Disconnected from WebSocket server.');
    };

    function addAppointment() {
        const name = document.getElementById('appointmentNameInput').value;
        const description = document.getElementById('appointmentDescriptionInput').value;
        const start_time = document.getElementById('startTimeInput').value;
        const end_time = document.getElementById('endTimeInput').value;
        const color = document.getElementById('appointmentColorInput').value;

        if (name == '' || start_time == '' || end_time == '' || color == '') {
            errorAlert.style.display = "block";
            errorAlert.textContent = "Please fill in all fields";
            closeModal()
            setTimeout(() => {
                errorAlert.style.display = "none";
            }, 5000);
            return;
        }
        ws.send(JSON.stringify({ action: 'make-appointment', room: <?php echo App\Application\Request::getParam('id'); ?>, agenda_id: <?php echo App\Application\Request::getParam('id'); ?>, name, description, start_time, end_time, color }));
        closeModal()
    }

    function editAppointment() {
        const name = document.getElementById('appointmentNameInput').value;
        const description = document.getElementById('appointmentDescriptionInput').value;
        const start_time = document.getElementById('startTimeInput').value;
        const end_time = document.getElementById('endTimeInput').value;
        const color = document.getElementById('appointmentColorInput').value;
        const id = document.getElementById('deleteButton').getAttribute('data-id');
        if (name == '' || start_time == '' || end_time == '' || color == '') {
            errorAlert.style.display = "block";
            errorAlert.textContent = "Please fill in all fields";
            closeModal()
            setTimeout(() => {
                errorAlert.style.display = "none";
            }, 5000);
            return;
        }
        ws.send(JSON.stringify({ action: 'update-appointment', room: <?php echo App\Application\Request::getParam('id'); ?>, id, name, description, start_time, end_time, color }));
        closeModal()
    }

    function removeAppointment() {
        const id = document.getElementById('deleteButton').getAttribute('data-id');
        ws.send(JSON.stringify({ action: 'remove-appointment', room: <?php echo App\Application\Request::getParam('id'); ?>, id }));
    }

    function closeModal() {
        const addAppointmentModal = document.getElementById('addAppointmentModal');
        let modalInstance = bootstrap.Modal.getInstance(addAppointmentModal);
        if (!modalInstance) {
            modalInstance = new bootstrap.Modal(addAppointmentModal);
        }

        modalInstance.hide();
    }

    function clearModal() {
        const name = document.getElementById('appointmentNameInput').value;
        const description = document.getElementById('appointmentDescriptionInput').value;
        const start_time = document.getElementById('startTimeInput').value;
        const end_time = document.getElementById('endTimeInput').value;

        document.getElementById('appointmentNameInput').value = '';
        document.getElementById('appointmentDescriptionInput').value = '';
        document.getElementById('startTimeInput').value = "2025-01-01T00:00";
        document.getElementById('endTimeInput').value = "2025-01-01T00:00";

    }

    function getAppointments(data) {
        // Function to parse date strings
        function parseDate(dateString) {
            return new Date(dateString.replace(".000000", ""));
        }

        // Convert UTC date to local time string
        function toLocalDateString(date) {
            return date.toLocaleString();
        }

        // Sort events by start_time
        const sortedEvents = data.sort((a, b) => {
            return parseDate(a.start_time.date) - parseDate(b.start_time.date);
        });

        // Process events to add spanning information and group by day
        const eventsByDay = {};

        sortedEvents.forEach((event) => {
            const startDate = parseDate(event.start_time.date);
            const endDate = parseDate(event.end_time.date);

            // Check if the event spans multiple days
            const isMultiDay =
                startDate.toISOString().slice(0, 10) !== endDate.toISOString().slice(0, 10);

            if (isMultiDay) {
                const daysSpanned = Math.ceil((endDate - startDate) / (1000 * 60 * 60 * 24));

                for (let i = 0; i < daysSpanned; i++) {
                    const currentDay = new Date(startDate);
                    currentDay.setDate(startDate.getDate() + i);

                    const formattedDate = currentDay.toISOString().slice(0, 10);
                    if (!eventsByDay[formattedDate]) {
                        eventsByDay[formattedDate] = [];
                    }

                    eventsByDay[formattedDate].push({
                        ...event,
                        name: `${event.name} (Day ${i + 1}/${daysSpanned})`,
                        start_time: toLocalDateString(startDate),
                        end_time: toLocalDateString(endDate)
                    });
                }
            } else {
                const formattedDate = startDate.toISOString().slice(0, 10); // Only the date part
                if (!eventsByDay[formattedDate]) {
                    eventsByDay[formattedDate] = [];
                }

                eventsByDay[formattedDate].push({
                    ...event,
                    start_time: toLocalDateString(startDate),
                    end_time: toLocalDateString(endDate)
                });
            }
        });

        // Generate HTML structure
        const calendarDiv = document.getElementById('calendar');
        if (!calendarDiv) {
            console.error("No element with id 'calendar' found.");
            return;
        }
        calendarDiv.innerHTML = '';
        const eweek = parseInt(searchParams.get("week"), 10);
        const eyear = parseInt(searchParams.get("year"), 10);

        Object.keys(eventsByDay).forEach((date) => {
            const dateObj = new Date(date);
            const { week, year } = getWeekYear(dateObj);

            // Check if the event belongs to the current week and year
            if (year === eyear && week === eweek) {
                const dateDiv = document.createElement('div');
                dateDiv.className = 'my-3';

                const dateParagraph = document.createElement('h5');
                dateParagraph.className = 'mb-3';
                dateParagraph.textContent = dateObj.toLocaleDateString(undefined, {
                    weekday: 'long',
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric',
                });

                const hr = document.createElement('hr');

                const eventsDiv = document.createElement('div');

                eventsByDay[date].forEach((event) => {
                    const details = document.createElement('div');
                    details.className = 'p-3 rounded-4 mb-2';
                    details.style.backgroundColor = event.color;
                    details.style.cursor = 'pointer';

                    const formatDateTime = (dateTimeString) => {
                        const date = new Date(dateTimeString);
                        const year = date.getFullYear();
                        const month = String(date.getMonth() + 1).padStart(2, '0'); // Month is zero-based
                        const day = String(date.getDate()).padStart(2, '0');
                        const hours = String(date.getHours()).padStart(2, '0');
                        const minutes = String(date.getMinutes()).padStart(2, '0');
                        return `${year}-${month}-${day}T${hours}:${minutes}`;
                    };

                    details.addEventListener('click', () => {
                        const addAppointmentModal = document.getElementById('addAppointmentModal');
                        editButton.style.display = "block";
                        deleteButton.style.display = "block";
                        addButton.style.display = "none";
                        document.getElementById('appointmentNameInput').value = event.name.split(' (Day ')[0];
                        document.getElementById('appointmentDescriptionInput').value = event.description;
                        document.getElementById('startTimeInput').value = formatDateTime(event.start_time);
                        document.getElementById('endTimeInput').value = formatDateTime(event.end_time);
                        document.getElementById('appointmentColorInput').value = event.color;
                        deleteButton.setAttribute("data-id", event.id);
                        let modalInstance = bootstrap.Modal.getInstance(addAppointmentModal);
                        if (!modalInstance) {
                            modalInstance = new bootstrap.Modal(addAppointmentModal);
                        }
                        modalInstance.show();
                    })


                    const dateSummary = document.createElement('p');
                    dateSummary.className = 'mb-1';
                    dateSummary.textContent = `${event.start_time} - ${event.end_time} ${event.name}`;

                    const description = document.createElement('p');
                    description.className = 'mb-0';
                    description.textContent = event.description || 'No description available';

                    details.appendChild(dateSummary);
                    details.appendChild(description);
                    eventsDiv.appendChild(details);
                });

                dateDiv.appendChild(hr);
                dateDiv.appendChild(dateParagraph);
                dateDiv.appendChild(eventsDiv);
                calendarDiv.appendChild(dateDiv);
            }
        });
    }
    function getWeekYear(date) {
        const d = new Date(date);

        // Get the first day of the week
        d.setDate(d.getDate() - (d.getDay() + 6) % 7 + 3);

        // Get the first day of the year
        const startOfYear = new Date(d.getFullYear(), 0, 1);

        // Calculate the number of days since the start of the year, then get the week number
        const weekNumber = Math.ceil(((d - startOfYear) / 86400000 + 1) / 7);

        return { week: weekNumber, year: d.getFullYear() };
    }

    function updateDisplay() {
        const { week, year } = getWeekYear(currentDate);
        weekDisplay.textContent = `Week ${week}, Year ${year}`;
        updateURLParams(week, year);
        setTimeout(() => {
            ws.send(JSON.stringify({ action: 'appointments', id: <?php echo App\Application\Request::getParam('id'); ?>, week: searchParams.get('week'), year: searchParams.get('year') }));
        }, 100);
    }

    function adjustWeek(weeks) {
        currentDate.setDate(currentDate.getDate() + weeks * 7);
        updateDisplay();
    }

    function updateURLParams(week, year) {
        searchParams.set('week', week);
        searchParams.set('year', year);
        const url = `${window.location.pathname}?${searchParams.toString()}`;
        window.history.replaceState({}, '', url);
    }

    function initializeFromURL() {
        const week = searchParams.get('week');
        const year = searchParams.get('year');

        if (week && year) {
            // Set the current date to the specified week and year
            const startOfYear = new Date(year, 0, 1);
            currentDate = new Date(startOfYear.getTime() + (week - 1) * 7 * 24 * 60 * 60 * 1000);
        } else {
            currentDate = new Date();
        }
    }

    let currentDate;

    initializeFromURL();
    updateDisplay();

    document.getElementById('prevWeek').addEventListener('click', () => adjustWeek(-1));
    document.getElementById('nextWeek').addEventListener('click', () => adjustWeek(1));
    document.getElementById('addAppointmentModal').addEventListener('hide.bs.modal', () => {
        editButton.style.display = "none";
        deleteButton.style.display = "none";
        addButton.style.display = "block";
        clearModal();
    });
</script>