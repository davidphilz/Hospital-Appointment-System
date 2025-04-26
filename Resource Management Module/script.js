function loadSignup() {
    $('#container').addClass('hidden-content');
    $.ajax({
        url: 'signup.php',
        type: 'GET',
        success: function (response) {
            $('#signupModal .modal-body').html(response);
            $('#signupModal').modal('show');
        },
        error: function (xhr, status, error) {
            console.error('Error loading signup.php:', error);
        }
    });
}

$('#loginform').submit(function (e) {
    e.preventDefault();
    var data = $(this).serialize();
    
    $.ajax({
        url: 'adminlogin.php',
        type: 'POST',
        data: data,
        dataType: 'json',
        success: function (response) {
            if (response.success) {
                switch (response.role) {
                    case 'admin':
                        window.location.href = 'adminlogin.php';
                        break;
                    default:
                        console.error('Invalid role:', response.role);
                        alert('Invalid user role');
                }
            } else {
                alert('Invalid admin information');
            }
        },
        error: function (xhr, status, error) {
            console.error('AJAX error:', error);
        }
    });
});

function validateLoginForm() {
    var username = document.getElementById("uname").value.trim();
    var password = document.getElementById("pass").value.trim();

    if (username === "") {
        alert('Please enter a username');
        return false;
    }
    if (password === "") {
        alert('Please enter a password');
        return false;
    }
    document.getElementById('loginform').submit();
}
