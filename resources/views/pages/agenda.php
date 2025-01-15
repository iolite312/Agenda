<h2>Agenda</h2>
<div id="calendar">
</div>
<script>
    const searchParams = new URLSearchParams(window.location.search);
    const ws = new WebSocket('ws://192.168.178.182:8082');

    // Listen for connection open
    ws.onopen = () => {
        console.log('Connected to WebSocket server.');
        ws.send(JSON.stringify({ action: 'join', room: <?php echo App\Application\Request::getParam('id'); ?> }));
        ws.send(JSON.stringify({ action: 'appointments', id: <?php echo App\Application\Request::getParam('id'); ?>, week: searchParams.get('week'), year: searchParams.get('year') }));
    };

    ws.onmessage = (event) => {
        const response = JSON.parse(event.data);
        if (response.trigger === 'appointments') {
            getAppointments(response.appointments);
            return;
        }

        if (response.trigger === 'update') {
            ws.send(JSON.stringify({ action: 'appointments', id: <?php echo App\Application\Request::getParam('id'); ?>, week: searchParams.get('week'), year: searchParams.get('year') }));
            getAppointments(response.appointments);
            return;
        }
        console.log(response);
    };

    // Listen for errors
    ws.onerror = (error) => {
        console.error('WebSocket error:', error);
    };

    // Listen for connection close
    ws.onclose = () => {
        console.log('Disconnected from WebSocket server.');
    };
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
        Object.keys(eventsByDay).forEach((date) => {
            const dateDiv = document.createElement('div');

            const dateParagraph = document.createElement('p');
            const dateObj = new Date(date);
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
        });
    }
</script>