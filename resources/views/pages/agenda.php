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
            <button class="btn btn-primary d-flex align-items-center" data-bs-toggle="modal"
                data-bs-target="#addAppointmentModal"><img src="/assets/images/plus.svg" alt="plus"></button>
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
                        <input type="datetime-local" id="startTimeInput" class="form-control"
                            placeholder="Appointment start_time">
                    </div>
                    <div class="input-group mb-3">
                        <span class="input-group-text" id="endtimeLabel">End time</span>
                        <input type="datetime-local" id="endTimeInput" class="form-control"
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
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="addAppointment()">Add appointment</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    const searchParams = new URLSearchParams(window.location.search);
    const weekDisplay = document.getElementById('weekDisplay');
    const successAlert = document.getElementById("successAlert");
    const errorAlert = document.getElementById("errorAlert");
    const ws = new WebSocket('ws://192.168.178.182:8082');

    // Listen for connection open
    ws.onopen = () => {
        console.log('Connected to WebSocket server.');
        ws.send(JSON.stringify({ action: 'join', room: <?php echo App\Application\Request::getParam('id'); ?> }));
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
                    }, 3000);
                } else {
                    errorAlert.style.display = "block";
                    errorAlert.textContent = "Error creating appointment";
                    setTimeout(() => {
                        errorAlert.style.display = "none";
                    }, 3000);
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
        const addAppointmentModal = document.getElementById('addAppointmentModal');
        ws.send(JSON.stringify({ action: 'make-appointment', room: <?php echo App\Application\Request::getParam('id'); ?>, agenda_id: <?php echo App\Application\Request::getParam('id'); ?>, name, description, start_time, end_time, color }));
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

                    const formattedDate = currentDay;
                    if (!eventsByDay[formattedDate]) {
                        eventsByDay[formattedDate] = [];
                    }

                    eventsByDay[formattedDate].push({
                        ...event,
                        name: `${event.name} ${i + 1}/${daysSpanned}`,
                        start_time: toLocalDateString(startDate),
                        end_time: toLocalDateString(endDate)
                    });
                }
            } else {
                const formattedDate = startDate.toISOString().slice(0, 10);
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

                const dateParagraph = document.createElement('p');
                dateParagraph.textContent = dateObj.toLocaleDateString(undefined, {
                    weekday: 'long',
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric',
                });

                const hr = document.createElement('hr');

                const eventsDiv = document.createElement('div');
                eventsDiv.className = 'events';

                eventsByDay[date].forEach((event) => {
                    const details = document.createElement('details');

                    const summary = document.createElement('summary');
                    summary.textContent = `${event.start_time} - ${event.end_time} ${event.name}`;

                    const description = document.createElement('p');
                    description.textContent = event.description || 'No description available';

                    details.appendChild(summary);
                    details.appendChild(description);
                    eventsDiv.appendChild(details);
                });

                dateDiv.appendChild(dateParagraph);
                dateDiv.appendChild(hr);
                dateDiv.appendChild(eventsDiv);
                calendarDiv.appendChild(dateDiv);
            }
        });
    }
    function getWeekYear(date) {
        const d = new Date(date);

        // Adjust date to Thursday (ISO 8601 week starts on Monday, but we use Thursday for consistency)
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
</script>