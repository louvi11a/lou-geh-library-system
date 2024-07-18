$(document).ready(function() {
    // Fetch and display reader profile information
    $.ajax({
        type: 'POST',
        url: '../controllers/UserController.php',
        data: { action: 'getReaderProfile' },
        success: function(response) {
            var profile = JSON.parse(response);
            $('#readerName').text(profile.first_name);
            $('#familyName').text(profile.family_name);
            $('#firstName').text(profile.first_name);
            $('#city').text(profile.city);
            $('#dob').text(profile.dob);
        }
    });

    // Fetch and display borrowed books
    $.ajax({
        type: 'POST',
        url: '../controllers/BorrowController.php',
        data: { action: 'getBorrowedBooks' },
        success: function(response) {
            var borrowedBooks = JSON.parse(response);
            borrowedBooks.forEach(function(book) {
                $('#borrowedBooksList').append('<li>' + book.title + ' by ' + book.author + ' (ISBN: ' + book.isbn + ', Copy No: ' + book.copy_number + ', Return Date: ' + book.return_date + ')</li>');
            });
        }
    });

    // Search books
    $('#searchButton').click(function() {
        var query = $('#searchQuery').val();
        $.ajax({
            type: 'POST',
            url: '../controllers/BookController.php',
            data: { action: 'searchBooks', query: query },
            success: function(response) {
                var books = JSON.parse(response);
                $('#searchResults').empty();
                books.forEach(function(book) {
                    $('#searchResults').append('<li>' + book.title + ' by ' + book.author + ' (ISBN: ' + book.isbn + ')</li>');
                });
            }
        });
    });

    // Fetch and display borrow history
    $.ajax({
        type: 'POST',
        url: '../controllers/BorrowController.php',
        data: { action: 'getBorrowHistory' },
        success: function(response) {
            var borrowHistory = JSON.parse(response);
            borrowHistory.forEach(function(record) {
                $('#borrowHistoryList').append('<li>' + record.title + ' by ' + record.author + ' (ISBN: ' + record.isbn + ', Borrowed on: ' + record.borrow_date + ', Returned on: ' + record.return_date + ')</li>');
            });
        }
    });

    // Logout
    $('#logoutButton').click(function() {
        $.ajax({
            type: 'POST',
            url: '../controllers/UserController.php',
            data: { action: 'logout' },
            success: function() {
                window.location.href = '../index.html';
            }
        });
    });
});
