// Set today's date as minimum date
const dateSelect = document.getElementById('dateSelect');

// Get today's date in YYYY-MM-DD format
function getTodayDate() {
    const today = new Date();
    const year = today.getFullYear();
    const month = String(today.getMonth() + 1).padStart(2, '0');
    const day = String(today.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}

// Check date selection when page loads
function initializeDatePicker() {
    const today = getTodayDate();
    dateSelect.min = today;
    dateSelect.value = today; // Set today's date as default value
    
    // Special format for mobile devices
    if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
        dateSelect.setAttribute('data-date', today);
        dateSelect.type = 'date';
        
        // Add special style for mobile view
        dateSelect.style.webkitAppearance = 'none';
        dateSelect.style.mozAppearance = 'none';
        dateSelect.style.appearance = 'none';
    }
    
    // If a selected date is in the past, clear it
    if (dateSelect.value && dateSelect.value < today) {
        dateSelect.value = '';
    }
}

// Run when page loads
document.addEventListener('DOMContentLoaded', initializeDatePicker);

// Check date when it changes
dateSelect.addEventListener('change', function() {
    const today = getTodayDate();
    if (this.value < today) {
        alert('You cannot select a past date!');
        this.value = today; // Reset to today's date if invalid date is selected
        return;
    }
    checkWeekend(this);
});

let selectedDate = '';
let selectedTime = '';

// Check for weekends
function checkWeekend(input) {
    const date = new Date(input.value);
    const weekendError = document.getElementById('weekendError');
    
    if (date.getDay() === 0) { // 0 = Pazar
        weekendError.style.display = 'block';
        input.value = ''; // Clear date
        return false;
    }
    
    weekendError.style.display = 'none';
    return true;
}

// Check if time is in the past
function isTimeInPast(timeStr, dateStr) {
    const [hours, minutes] = timeStr.trim().split(':');
    const selectedDateTime = new Date(dateStr);
    selectedDateTime.setHours(parseInt(hours), parseInt(minutes), 0);
    
    const now = new Date();
    return selectedDateTime < now;
}

// Go to next step
function nextStep(currentStep) {
    if (currentStep === 1) {
        selectedDate = document.getElementById('dateSelect').value;
        if (!selectedDate) {
            alert('Please select a date!');
            return;
        }
        
        // Check for weekends
        const date = new Date(selectedDate);
        if (date.getDay() === 0) {
            alert('We do not offer services on Sundays. Please select another day.');
            return;
        }
        
        // Get times from PHP when date is selected
        const timeSlots = document.getElementById('timeSlots');
        timeSlots.innerHTML = '<div class="loading">Loading...</div>';
        
        // Get times from PHP when date is selected
        fetch('get-available-times.php?date=' + selectedDate)
            .then(response => response.text())
            .then(html => {
                timeSlots.innerHTML = html;
                
                // If today's date is selected, disable past time slots
                if (selectedDate === getTodayDate()) {
                    document.querySelectorAll('.time-slot').forEach(slot => {
                        const timeStr = slot.textContent.trim();
                        if (isTimeInPast(timeStr, selectedDate)) {
                            slot.classList.add('booked');
                            slot.style.opacity = '0.5';
                            slot.style.cursor = 'not-allowed';
                        }
                    });
                }
                
                document.getElementById('step1').style.display = 'none';
                document.getElementById('step2').style.display = 'block';
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error loading times!');
            });
    } else if (currentStep === 2) {
        if (!selectedTime) {
            alert('Please select a time!');
            return;
        }
        document.getElementById(`step${currentStep}`).style.display = 'none';
        document.getElementById(`step${currentStep + 1}`).style.display = 'block';
    }
}

// Go back to previous step
function prevStep(currentStep) {
    document.getElementById(`step${currentStep}`).style.display = 'none';
    document.getElementById(`step${currentStep - 1}`).style.display = 'block';
}

// Select time slot
function selectTimeSlot(slot) {
    if (!slot.classList.contains('booked')) {
        const timeStr = slot.textContent.trim();
        
        // Check if the selected time is in the past for today's date
        if (selectedDate === getTodayDate() && isTimeInPast(timeStr, selectedDate)) {
            alert('This time is in the past, please select a later time.');
            return;
        }
        
        document.querySelectorAll('.time-slot').forEach(s => s.classList.remove('selected'));
        slot.classList.add('selected');
        selectedTime = timeStr;
    }
}

// Form submission
document.getElementById('appointmentForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Check date and time
    if (!selectedDate || !selectedTime) {
        alert('Please select both date and time!');
        return;
    }


    const formData = new FormData();
    formData.append('date', selectedDate);
    formData.append('time', selectedTime);
    formData.append('name', document.getElementById('name').value);
    formData.append('phone', document.getElementById('phone').value);
    formData.append('email', document.getElementById('email').value);

    // AJAX request to save appointment
    fetch('save-appointment.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('step3').innerHTML = `
                <h2>Appointment created successfully</h2>
                <p>Details sent to your email address.</p>
                <p class="redirect-message">Redirecting to main page...</p>
            `;
            
            // Redirect after 5 seconds
            let countdown = 5;
            const redirectMessage = document.querySelector('.redirect-message');
            
            const countdownInterval = setInterval(() => {
                countdown--;
                if (countdown > 0) {
                    redirectMessage.textContent = `${countdown} seconds until redirect...`;
                } else {
                    clearInterval(countdownInterval);
                    window.location.replace('https://your-company.com');
                }
            }, 1000);
        } else {
            alert(data.error || 'An error occurred!');

        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred!');
    });
});

// sprintf function
function sprintf(format, ...args) {
    let i = 0;
    return format.replace(/%s|%d/g, () => args[i++]);
} 