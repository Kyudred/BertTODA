document.addEventListener('DOMContentLoaded', function() {
    // Current displayed date
    let currentDate = new Date();

    // Sample events data (in a real application, this would come from a database)
    let events = [
        {
            id: 1,
            title: 'Youth Leadership Workshop',
            date: '2025-05-10',
            time: '09:00',
            location: 'Barangay Hall',
            description: 'Interactive workshop for youth leaders in the community.',
            category: 'education'
        },
        {
            id: 2,
            title: 'Basketball Tournament',
            date: '2025-05-15',
            time: '14:00',
            location: 'Barangay Basketball Court',
            description: 'Annual youth basketball competition between sitios.',
            category: 'sports'
        },
        {
            id: 3,
            title: 'Community Clean-up Drive',
            date: '2025-05-22',
            time: '07:00',
            location: 'San Isidro Park',
            description: 'Monthly clean-up drive to maintain our community spaces.',
            category: 'community'
        },
        {
            id: 4,
            title: 'SK Council Meeting',
            date: '2025-05-05',
            time: '16:00',
            location: 'SK Office',
            description: 'Regular monthly meeting of the SK council.',
            category: 'meeting'
        },
        {
            id: 5,
            title: 'Cultural Night',
            date: '2025-05-30',
            time: '18:00',
            location: 'Barangay Plaza',
            description: 'Celebrating local culture with performances and exhibits.',
            category: 'cultural'
        }
    ];

    // DOM Elements
    const calendarEl = document.getElementById('calendar');
    const monthYearEl = document.getElementById('monthYear');
    const prevMonthBtn = document.getElementById('prevMonth');
    const nextMonthBtn = document.getElementById('nextMonth');
    const addEventBtn = document.getElementById('addEventBtn');
    const eventModal = document.getElementById('eventModal');
    const eventDetailsModal = document.getElementById('eventDetailsModal');
    const upcomingEventsEl = document.getElementById('upcomingEvents');

    // Event form elements
    const eventForm = document.getElementById('eventForm');
    const modalTitle = document.getElementById('modalTitle');
    const eventTitleInput = document.getElementById('eventTitle');
    const eventDateInput = document.getElementById('eventDate');
    const eventTimeInput = document.getElementById('eventTime');
    const eventLocationInput = document.getElementById('eventLocation');
    const eventDescriptionInput = document.getElementById('eventDescription');
    const eventCategoryInput = document.getElementById('eventCategory');
    const saveEventBtn = document.getElementById('saveEventBtn');
    const cancelEventBtn = document.getElementById('cancelEventBtn');

    // Event details elements
    const detailsTitleEl = document.getElementById('detailsTitle');
    const detailDateEl = document.getElementById('detailDate');
    const detailTimeEl = document.getElementById('detailTime');
    const detailLocationEl = document.getElementById('detailLocation');
    const detailCategoryEl = document.getElementById('detailCategory');
    const detailDescriptionEl = document.getElementById('detailDescription');
    const editEventBtn = document.getElementById('editEventBtn');
    const deleteEventBtn = document.getElementById('deleteEventBtn');
    const closeDetailsBtn = document.getElementById('closeDetailsBtn');

    // Modal close buttons
    const closeButtons = document.querySelectorAll('.close');

    // Initialize calendar
    renderCalendar(currentDate);
    updateUpcomingEvents();

    // Event listeners
    prevMonthBtn.addEventListener('click', () => {
        currentDate.setMonth(currentDate.getMonth() - 1);
        renderCalendar(currentDate);
    });

    nextMonthBtn.addEventListener('click', () => {
        currentDate.setMonth(currentDate.getMonth() + 1);
        renderCalendar(currentDate);
    });

    addEventBtn.addEventListener('click', () => {
        openAddEventModal();
    });

    eventForm.addEventListener('submit', (e) => {
        e.preventDefault();
        saveEvent();
    });

    cancelEventBtn.addEventListener('click', () => {
        eventModal.style.display = 'none';
    });

    closeDetailsBtn.addEventListener('click', () => {
        eventDetailsModal.style.display = 'none';
    });

    editEventBtn.addEventListener('click', () => {
        const eventId = parseInt(editEventBtn.dataset.eventId);
        const event = events.find(e => e.id === eventId);
        if (event) {
            openEditEventModal(event);
            eventDetailsModal.style.display = 'none';
        }
    });

    deleteEventBtn.addEventListener('click', () => {
        const eventId = parseInt(deleteEventBtn.dataset.eventId);
        if (confirm('Are you sure you want to delete this event?')) {
            deleteEvent(eventId);
            eventDetailsModal.style.display = 'none';
        }
    });

    closeButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            eventModal.style.display = 'none';
            eventDetailsModal.style.display = 'none';
        });
    });

    // Close modals when clicking outside
    window.addEventListener('click', (e) => {
        if (e.target === eventModal) {
            eventModal.style.display = 'none';
        }
        if (e.target === eventDetailsModal) {
            eventDetailsModal.style.display = 'none';
        }
    });

    // Functions
    function renderCalendar(date) {
        const year = date.getFullYear();
        const month = date.getMonth();
        
        // Update month and year display
        const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 
                           'July', 'August', 'September', 'October', 'November', 'December'];
        monthYearEl.textContent = `${monthNames[month]} ${year}`;
        
        // Clear calendar
        calendarEl.innerHTML = '';
        
        // Get first day of month and last day of month
        const firstDay = new Date(year, month, 1);
        const lastDay = new Date(year, month + 1, 0);
        
        // Get day of week for first day (0 = Sunday, 6 = Saturday)
        const firstDayOfWeek = firstDay.getDay();
        
        // Get total days in current month
        const daysInMonth = lastDay.getDate();
        
        // Get total days in previous month
        const prevLastDay = new Date(year, month, 0);
        const daysInPrevMonth = prevLastDay.getDate();
        
        // Calculate total cells needed (prev month days + current month days + next month days)
        const totalCells = Math.ceil((firstDayOfWeek + daysInMonth) / 7) * 7;
        
        // Current date for highlighting today
        const today = new Date();
        const currentDay = today.getDate();
        const currentMonth = today.getMonth();
        const currentYear = today.getFullYear();
        
        // Generate calendar cells
        for (let i = 0; i < totalCells; i++) {
            // Calculate day to display
            let displayDay, displayMonth, displayYear;
            let isOtherMonth = false;
            
            // Previous month days
            if (i < firstDayOfWeek) {
                displayDay = daysInPrevMonth - (firstDayOfWeek - i - 1);
                displayMonth = month - 1;
                displayYear = year;
                if (displayMonth < 0) {
                    displayMonth = 11;
                    displayYear--;
                }
                isOtherMonth = true;
            }
            // Next month days
            else if (i >= firstDayOfWeek + daysInMonth) {
                displayDay = i - (firstDayOfWeek + daysInMonth) + 1;
                displayMonth = month + 1;
                displayYear = year;
                if (displayMonth > 11) {
                    displayMonth = 0;
                    displayYear++;
                }
                isOtherMonth = true;
            }
            // Current month days
            else {
                displayDay = i - firstDayOfWeek + 1;
                displayMonth = month;
                displayYear = year;
            }
            
            // Create day cell
            const dayCell = document.createElement('div');
            dayCell.className = 'day-cell';
            
            // Add 'today' class if it's today
            if (displayDay === currentDay && displayMonth === currentMonth && displayYear === currentYear) {
                dayCell.classList.add('today');
            }
            
            // Add 'other-month' class if it's not in the current month
            if (isOtherMonth) {
                dayCell.classList.add('other-month');
            }
            
            // Create day number
            const dayNumber = document.createElement('div');
            dayNumber.className = 'day-number';
            dayNumber.textContent = displayDay;
            dayCell.appendChild(dayNumber);
            
            // Format date string for comparison with events
            const dateStr = `${displayYear}-${String(displayMonth + 1).padStart(2, '0')}-${String(displayDay).padStart(2, '0')}`;
            
            // Find events for this day
            const dayEvents = events.filter(event => event.date === dateStr);
            
            // Add events to day cell
            dayEvents.forEach(event => {
                const eventEl = document.createElement('div');
                eventEl.className = `event-item event-${event.category}`;
                eventEl.textContent = event.title;
                eventEl.dataset.eventId = event.id;
                
                // Add click event to show event details
                eventEl.addEventListener('click', () => {
                    showEventDetails(event);
                });
                
                dayCell.appendChild(eventEl);
            });
            
            // Add day cell to calendar
            calendarEl.appendChild(dayCell);
        }
    }
    
    function updateUpcomingEvents() {
        upcomingEventsEl.innerHTML = '';
        
        // Get current date without time
        const now = new Date();
        const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
        
        // Filter and sort upcoming events
        const upcoming = events
            .filter(event => new Date(event.date) >= today)
            .sort((a, b) => new Date(a.date) - new Date(b.date))
            .slice(0, 5); // Show only 5 upcoming events
        
        if (upcoming.length === 0) {
            const noEvents = document.createElement('p');
            noEvents.textContent = 'No upcoming events.';
            upcomingEventsEl.appendChild(noEvents);
            return;
        }
        
        // Display upcoming events
        upcoming.forEach(event => {
            const eventCard = document.createElement('div');
            eventCard.className = 'event-card';
            eventCard.dataset.eventId = event.id;
            
            // Format date for display
            const eventDate = new Date(event.date);
            const formattedDate = eventDate.toLocaleDateString('en-US', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
            
            eventCard.innerHTML = `
                <h3>${event.title}</h3>
                <div class="event-meta">
                    <span><i class="fas fa-calendar"></i> ${formattedDate}</span>
                    <span><i class="fas fa-clock"></i> ${formatTime(event.time)}</span>
                </div>
                <div class="event-meta">
                    <span><i class="fas fa-map-marker-alt"></i> ${event.location}</span>
                </div>
                <span class="event-category event-${event.category}">${capitalizeFirstLetter(event.category)}</span>
            `;
            
            // Add click event to show details
            eventCard.addEventListener('click', () => {
                showEventDetails(event);
            });
            
            upcomingEventsEl.appendChild(eventCard);
        });
    }
    
    function openAddEventModal() {
        // Reset form
        eventForm.reset();
        
        // Set current date as default
        const today = new Date();
        const year = today.getFullYear();
        const month = String(today.getMonth() + 1).padStart(2, '0');
        const day = String(today.getDate()).padStart(2, '0');
        eventDateInput.value = `${year}-${month}-${day}`;
        
        // Update modal title
        modalTitle.textContent = 'Add New Event';
        
        // Clear event ID
        saveEventBtn.dataset.eventId = '';
        
        // Show modal
        eventModal.style.display = 'block';
    }
    
    function openEditEventModal(event) {
        // Fill form with event data
        eventTitleInput.value = event.title;
        eventDateInput.value = event.date;
        eventTimeInput.value = event.time;
        eventLocationInput.value = event.location;
        eventDescriptionInput.value = event.description;
        eventCategoryInput.value = event.category;
        
        // Update modal title
        modalTitle.textContent = 'Edit Event';
        
        // Set event ID
        saveEventBtn.dataset.eventId = event.id;
        
        // Show modal
        eventModal.style.display = 'block';
    }
    
    function saveEvent() {
        // Get form values
        const title = eventTitleInput.value.trim();
        const date = eventDateInput.value;
        const time = eventTimeInput.value;
        const location = eventLocationInput.value.trim();
        const description = eventDescriptionInput.value.trim();
        const category = eventCategoryInput.value;
        
        // Validate form
        if (!title || !date || !time || !location) {
            alert('Please fill in all required fields');
            return;
        }
        
        // Check if editing or adding
        const eventId = saveEventBtn.dataset.eventId;
        
        if (eventId) {
            // Update existing event
            const index = events.findIndex(e => e.id === parseInt(eventId));
            if (index !== -1) {
                events[index] = {
                    id: parseInt(eventId),
                    title,
                    date,
                    time,
                    location,
                    description,
                    category
                };
            }
        } else {
            // Create new event
            const newId = events.length > 0 ? Math.max(...events.map(e => e.id)) + 1 : 1;
            events.push({
                id: newId,
                title,
                date,
                time,
                location,
                description,
                category
            });
        }
        
        // Hide modal
        eventModal.style.display = 'none';
        
        // Update calendar and events list
        renderCalendar(currentDate);
        updateUpcomingEvents();
        
        // Show success message
        alert(eventId ? 'Event updated successfully!' : 'Event added successfully!');
    }
    
    function deleteEvent(eventId) {
        // Find event index
        const index = events.findIndex(e => e.id === eventId);
        
        if (index !== -1) {
            // Remove event
            events.splice(index, 1);
            
            // Update calendar and events list
            renderCalendar(currentDate);
            updateUpcomingEvents();
            
            // Show success message
            alert('Event deleted successfully!');
        }
    }
    
    function showEventDetails(event) {
        // Fill details
        detailsTitleEl.textContent = event.title;
        
        // Format date for display
        const eventDate = new Date(event.date);
        const formattedDate = eventDate.toLocaleDateString('en-US', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
        
        detailDateEl.textContent = formattedDate;
        detailTimeEl.textContent = formatTime(event.time);
        detailLocationEl.textContent = event.location;
        detailCategoryEl.textContent = capitalizeFirstLetter(event.category);
        detailCategoryEl.className = `event-category event-${event.category}`;
        detailDescriptionEl.textContent = event.description || 'No description provided.';
        
        // Set event ID for edit and delete buttons
        editEventBtn.dataset.eventId = event.id;
        deleteEventBtn.dataset.eventId = event.id;
        
        // Show modal
        eventDetailsModal.style.display = 'block';
    }
    
    // Helper functions
    function formatTime(timeStr) {
        // Convert 24-hour format to 12-hour format
        const [hours, minutes] = timeStr.split(':');
        const hour = parseInt(hours);
        const ampm = hour >= 12 ? 'PM' : 'AM';
        const hour12 = hour % 12 || 12;
        return `${hour12}:${minutes} ${ampm}`;
    }
    
    function capitalizeFirstLetter(string) {
        return string.charAt(0).toUpperCase() + string.slice(1);
    }
});