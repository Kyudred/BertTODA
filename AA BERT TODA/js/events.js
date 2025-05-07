document.addEventListener('DOMContentLoaded', function() {
    // Initialize calendar
    let currentDate = new Date();
    let selectedDate = null;
    let events = loadEvents();
    let editingEventId = null;
    
    // DOM Elements
    const calendarGrid = document.getElementById('calendar-grid');
    const currentMonthElement = document.getElementById('current-month');
    const prevMonthButton = document.getElementById('prev-month');
    const nextMonthButton = document.getElementById('next-month');
    const addEventBtn = document.getElementById('add-event-btn');
    const eventModal = document.getElementById('event-modal');
    const eventDetailsModal = document.getElementById('event-details-modal');
    const eventForm = document.getElementById('event-form');
    const saveEventBtn = document.getElementById('save-event');
    const deleteEventBtn = document.getElementById('delete-event');
    const editEventBtn = document.getElementById('edit-event-btn');
    const modalTitle = document.getElementById('modal-title');
    const eventsList = document.getElementById('events-list');
    const closeModalButtons = document.querySelectorAll('.close-modal');
    
    // Initialize the calendar
    renderCalendar(currentDate);
    renderUpcomingEvents();
    
    // Event Listeners
    prevMonthButton.addEventListener('click', () => {
        currentDate.setMonth(currentDate.getMonth() - 1);
        renderCalendar(currentDate);
    });
    
    nextMonthButton.addEventListener('click', () => {
        currentDate.setMonth(currentDate.getMonth() + 1);
        renderCalendar(currentDate);
    });
    
    addEventBtn.addEventListener('click', () => {
        openAddEventModal();
    });
    
    closeModalButtons.forEach(button => {
        button.addEventListener('click', () => {
            eventModal.style.display = 'none';
            eventDetailsModal.style.display = 'none';
        });
    });
    
    eventForm.addEventListener('submit', (e) => {
        e.preventDefault();
        saveEvent();
    });
    
    deleteEventBtn.addEventListener('click', () => {
        if (editingEventId) {
            deleteEvent(editingEventId);
        }
    });
    
    editEventBtn.addEventListener('click', () => {
        if (selectedEvent) {
            eventDetailsModal.style.display = 'none';
            openEditEventModal(selectedEvent);
        }
    });
    
    window.addEventListener('click', (e) => {
        if (e.target === eventModal) {
            eventModal.style.display = 'none';
        }
        if (e.target === eventDetailsModal) {
            eventDetailsModal.style.display = 'none';
        }
    });
    
    // Calendar Functions
    function renderCalendar(date) {
        const year = date.getFullYear();
        const month = date.getMonth();
        
        // Update the month and year display
        const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 
                           'July', 'August', 'September', 'October', 'November', 'December'];
        currentMonthElement.textContent = `${monthNames[month]} ${year}`;
        
        // Clear the calendar grid
        calendarGrid.innerHTML = '';
        
        // Get the first day of the month
        const firstDay = new Date(year, month, 1);
        const startingDay = firstDay.getDay(); // 0 = Sunday, 1 = Monday, etc.
        
        // Get the last day of the month
        const lastDay = new Date(year, month + 1, 0);
        const totalDays = lastDay.getDate();
        
        // Get the last day of the previous month
        const prevMonthLastDay = new Date(year, month, 0).getDate();
        
        // Get today's date for highlighting
        const today = new Date();
        const isCurrentMonth = today.getMonth() === month && today.getFullYear() === year;
        
        // Calculate total cells (previous month days + current month days + next month days)
        const totalCells = 42; // 6 rows x 7 days
        
        // Create calendar cells
        for (let i = 0; i < totalCells; i++) {
            const cell = document.createElement('div');
            cell.classList.add('calendar-day');
            
            // Previous month days
            if (i < startingDay) {
                const day = prevMonthLastDay - startingDay + i + 1;
                cell.classList.add('other-month');
                cell.innerHTML = `
                    <div class="day-number">${day}</div>
                    <div class="day-events"></div>
                `;
                
                // Add previous month date data
                const prevMonth = month === 0 ? 11 : month - 1;
                const prevYear = month === 0 ? year - 1 : year;
                cell.dataset.date = `${prevYear}-${(prevMonth + 1).toString().padStart(2, '0')}-${day.toString().padStart(2, '0')}`;
            } 
            // Current month days
            else if (i < startingDay + totalDays) {
                const day = i - startingDay + 1;
                
                // Check if it's today
                if (isCurrentMonth && day === today.getDate()) {
                    cell.classList.add('today');
                }
                
                cell.innerHTML = `
                    <div class="day-number">${day}</div>
                    <div class="day-events"></div>
                `;
                
                // Add current month date data
                cell.dataset.date = `${year}-${(month + 1).toString().padStart(2, '0')}-${day.toString().padStart(2, '0')}`;
                
                // Add click event to add event on this day
                cell.addEventListener('click', () => {
                    selectedDate = new Date(year, month, day);
                    openAddEventModal(selectedDate);
                });
            } 
            // Next month days
            else {
                const day = i - (startingDay + totalDays) + 1;
                cell.classList.add('other-month');
                cell.innerHTML = `
                    <div class="day-number">${day}</div>
                    <div class="day-events"></div>
                `;
                
                // Add next month date data
                const nextMonth = month === 11 ? 0 : month + 1;
                const nextYear = month === 11 ? year + 1 : year;
                cell.dataset.date = `${nextYear}-${(nextMonth + 1).toString().padStart(2, '0')}-${day.toString().padStart(2, '0')}`;
            }
            
            calendarGrid.appendChild(cell);
        }
        
        // Add events to the calendar
        addEventsToCalendar();
    }
    
    function addEventsToCalendar() {
        // Get all days in the calendar
        const days = document.querySelectorAll('.calendar-day');
        
        // Loop through each day
        days.forEach(day => {
            const dateStr = day.dataset.date;
            const dayEvents = day.querySelector('.day-events');
            
            // Find events for this day
            const eventsForDay = events.filter(event => {
                const eventDate = event.date.split('T')[0];
                return eventDate === dateStr;
            });
            
            // Add events to the day
            eventsForDay.forEach(event => {
                const eventDiv = document.createElement('div');
                eventDiv.classList.add('day-event', event.category);
                eventDiv.textContent = event.title;
                eventDiv.dataset.id = event.id;
                
                // Add click event to show event details
                eventDiv.addEventListener('click', (e) => {
                    e.stopPropagation();
                    showEventDetails(event);
                });
                
                dayEvents.appendChild(eventDiv);
            });
        });
    }
    
    function renderUpcomingEvents() {
        // Clear the events list
        eventsList.innerHTML = '';
        
        // Get today's date
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        
        // Filter events that are today or in the future, and sort by date
        const upcomingEvents = events
            .filter(event => {
                const eventDate = new Date(event.date);
                return eventDate >= today;
            })
            .sort((a, b) => new Date(a.date) - new Date(b.date));
        
        // Show only the next 5 events
        const eventsToShow = upcomingEvents.slice(0, 5);
        
        // Add events to the list
        if (eventsToShow.length === 0) {
            eventsList.innerHTML = '<p>No upcoming events</p>';
        } else {
            eventsToShow.forEach(event => {
                const eventDate = new Date(event.date);
                const formattedDate = eventDate.toLocaleDateString('en-US', { 
                    weekday: 'short', 
                    month: 'short', 
                    day: 'numeric' 
                });
                
                const eventCard = document.createElement('div');
                eventCard.classList.add('event-card', event.category);
                eventCard.dataset.id = event.id;
                
                eventCard.innerHTML = `
                    <div class="event-card-left">
                        <div class="event-title">${event.title}</div>
                        <div class="event-details">
                            <span><i class="fas fa-calendar-day"></i> ${formattedDate}</span>
                            <span><i class="fas fa-clock"></i> ${event.time}</span>
                            <span><i class="fas fa-map-marker-alt"></i> ${event.location}</span>
                        </div>
                    </div>
                    <div class="event-card-right">
                        <div class="event-category-badge ${event.category}">${event.category}</div>
                    </div>
                `;
                
                // Add click event to show event details
                eventCard.addEventListener('click', () => {
                    showEventDetails(event);
                });
                
                eventsList.appendChild(eventCard);
            });
        }
    }
    
    // Event Modal Functions
    let selectedEvent = null;
    
    function openAddEventModal(date = null) {
        // Reset form
        eventForm.reset();
        modalTitle.textContent = 'Add New Event';
        deleteEventBtn.style.display = 'none';
        editingEventId = null;
        
        // Set date if provided
        if (date) {
            const year = date.getFullYear();
            const month = (date.getMonth() + 1).toString().padStart(2, '0');
            const day = date.getDate().toString().padStart(2, '0');
            document.getElementById('event-date').value = `${year}-${month}-${day}`;
        } else {
            // Default to today
            const today = new Date();
            const year = today.getFullYear();
            const month = (today.getMonth() + 1).toString().padStart(2, '0');
            const day = today.getDate().toString().padStart(2, '0');
            document.getElementById('event-date').value = `${year}-${month}-${day}`;
        }
        
        // Show modal
        eventModal.style.display = 'block';
    }
    
    function openEditEventModal(event) {
        // Set form values
        modalTitle.textContent = 'Edit Event';
        document.getElementById('event-title').value = event.title;
        document.getElementById('event-date').value = event.date.split('T')[0];
        document.getElementById('event-time').value = event.time;
        document.getElementById('event-location').value = event.location;
        document.getElementById('event-description').value = event.description;
        document.getElementById('event-category').value = event.category;
        
        // Set selected items
        const itemsSelect = document.getElementById('event-items');
        for (let i = 0; i < itemsSelect.options.length; i++) {
            const option = itemsSelect.options[i];
            option.selected = event.items.includes(option.value);
        }
        
        // Show delete button
        deleteEventBtn.style.display = 'block';
        
        // Set editing event ID
        editingEventId = event.id;
        
        // Show modal
        eventModal.style.display = 'block';
    }
    
    function showEventDetails(event) {
        // Set selected event
        selectedEvent = event;
        
        // Set details in modal
        document.getElementById('details-title').textContent = event.title;
        
        const eventDate = new Date(event.date);
        const formattedDate = eventDate.toLocaleDateString('en-US', { 
            weekday: 'long', 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
        });
        
        document.getElementById('details-date').textContent = formattedDate;
        document.getElementById('details-time').textContent = event.time;
        document.getElementById('details-location').textContent = event.location;
        document.getElementById('details-description').textContent = event.description || 'No description provided';
        
        // Set category badge
        const categoryBadge = document.getElementById('details-category');
        categoryBadge.textContent = event.category;
        categoryBadge.className = `event-category-badge ${event.category}`;
        
        // Set items list
        const itemsList = document.getElementById('details-items');
        itemsList.innerHTML = '';
        
        if (event.items && event.items.length > 0) {
            document.getElementById('items-needed-row').style.display = 'flex';
            event.items.forEach(item => {
                const li = document.createElement('li');
                li.textContent = item.charAt(0).toUpperCase() + item.slice(1);
                itemsList.appendChild(li);
            });
        } else {
            document.getElementById('items-needed-row').style.display = 'none';
        }
        
        // Show modal
        eventDetailsModal.style.display = 'block';
    }
    
    function saveEvent() {
        // Get form values
        const title = document.getElementById('event-title').value;
        const date = document.getElementById('event-date').value;
        const time = document.getElementById('event-time').value;
        const location = document.getElementById('event-location').value;
        const description = document.getElementById('event-description').value;
        const category = document.getElementById('event-category').value;
        
        // Get selected items
        const itemsSelect = document.getElementById('event-items');
        const selectedItems = Array.from(itemsSelect.selectedOptions).map(option => option.value);
        
        // Create event object
        const event = {
            title,
            date,
            time,
            location,
            description,
            category,
            items: selectedItems
        };
        
        // Add or update event
        if (editingEventId) {
            event.id = editingEventId;
            updateEvent(event);
        } else {
            event.id = generateId();
            addEvent(event);
        }
        
        // Close modal
        eventModal.style.display = 'none';
    }
    
    function addEvent(event) {
        events.push(event);
        saveEvents();
        renderCalendar(currentDate);
        renderUpcomingEvents();
    }
    
    function updateEvent(updatedEvent) {
        const index = events.findIndex(event => event.id === updatedEvent.id);
        if (index !== -1) {
            events[index] = updatedEvent;
            saveEvents();
            renderCalendar(currentDate);
            renderUpcomingEvents();
        }
    }
    
    function deleteEvent(eventId) {
        const confirmed = confirm('Are you sure you want to delete this event?');
        if (confirmed) {
            events = events.filter(event => event.id !== eventId);
            saveEvents();
            renderCalendar(currentDate);
            renderUpcomingEvents();
            eventModal.style.display = 'none';
        }
    }
    
    // Helper Functions
    function generateId() {
        return Date.now().toString();
    }
    
    function saveEvents() {
        localStorage.setItem('skEvents', JSON.stringify(events));
    }
    
    function loadEvents() {
        const savedEvents = localStorage.getItem('skEvents');
        if (savedEvents) {
            return JSON.parse(savedEvents);
        }
        
        // Return sample events if no events are saved
        return getSampleEvents();
    }
    
    function getSampleEvents() {
        const today = new Date();
        const year = today.getFullYear();
        const month = today.getMonth();
        
        return [
            {
                id: '1',
                title: 'Youth Council Meeting',
                date: `${year}-${(month + 1).toString().padStart(2, '0')}-${(today.getDate() + 2).toString().padStart(2, '0')}`,
                time: '14:00',
                location: 'Barangay Hall',
                description: 'Monthly meeting to discuss upcoming youth programs and initiatives.',
                category: 'meeting',
                items: ['projector', 'microphone']
            },
            {
                id: '2',
                title: 'Basketball Tournament',
                date: `${year}-${(month + 1).toString().padStart(2, '0')}-${(today.getDate() + 5).toString().padStart(2, '0')}`,
                time: '09:00',
                location: 'Barangay Basketball Court',
                description: 'Annual youth basketball tournament with teams from neighboring barangays.',
                category: 'sports',
                items: ['basketball', 'chairs']
            },
            {
                id: '3',
                title: 'Leadership Workshop',
                date: `${year}-${(month + 1).toString().padStart(2, '0')}-${(today.getDate() + 10).toString().padStart(2, '0')}`,
                time: '13:00',
                location: 'Community Center',
                description: 'Workshop aimed at developing leadership skills among the youth.',
                category: 'educational',
                items: ['projector', 'microphone', 'tables', 'chairs']
            }
        ];
    }
});