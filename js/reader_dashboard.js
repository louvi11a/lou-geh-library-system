$(document).ready(function() {
    // Function to populate dropdown with search results
    function populateDropdown(books) {
        var dropdown = $('#searchResultsDropdown');
        dropdown.empty();
        dropdown.append($('<option>').text('Select a book...').attr('value', ''));
        books.forEach(function(book) {
            dropdown.append($('<option>').text(book.title + ' by ' + book.author).attr('value', book.isbn));
        });
    }

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
            console.log("Response:", response);
            try {
                var borrowedBooks = JSON.parse(response);
                borrowedBooks.forEach(function(book) {
                    $('#borrowedBooksList').append('<li>' + book.title + ' by ' + book.author + ' (ISBN: ' + book.isbn + ', Copy No: ' + book.copy_number + ', Return Date: ' + book.return_date + ')</li>');
                });
            } catch (e) {
                console.error("Parsing error:", e);
                console.error("Response:", response);
            }
        },
        error: function(xhr, status, error) {
            console.error("AJAX error:", error);
        }
    });

    // Fetch and display borrow history
    $.ajax({
        type: 'POST',
        url: '../controllers/BorrowController.php',
        data: { action: 'getBorrowHistory' },
        success: function(response) {
            console.log("Response:", response);
            try {
                var borrowHistory = JSON.parse(response);
                borrowHistory.forEach(function(book) {
                    $('#borrowHistoryList').append('<li>' + book.title + ' by ' + book.author + ' (ISBN: ' + book.isbn + ', Borrow Date: ' + book.borrow_date + ', Return Date: ' + book.return_date + ')</li>');
                });
            } catch (e) {
                console.error("Parsing error:", e);
                console.error("Response:", response);
            }
        },
        error: function(xhr, status, error) {
            console.error("AJAX error:", error);
        }
    });

    // Handle search button click
    $('#searchButton').click(function() {
        var query = $('#searchQuery').val().trim();
        if (query !== '') {
            $.ajax({
                type: 'POST',
                url: '../controllers/BookController.php',
                data: { action: 'searchBooks', query: query },
                success: function(response) {
                    try {
                        var books = JSON.parse(response);
                        // Clear previous results
                        $('#searchResultsDropdown').empty();
                        // Populate dropdown with search results
                        books.forEach(function(book) {
                            $('#searchResultsDropdown').append('<option value="' + book.isbn + '">' + book.title + ' by ' + book.author + '</option>');
                        });
                    } catch (e) {
                        console.error("Parsing error:", e);
                        console.error("Response:", response);
                    }
                },
                error: function(xhr, status, error) {
                    console.error("AJAX error:", error);
                }
            });
        } else {
            alert("Please enter a search query.");
        }
    });

    // Handle book selection from dropdown
    $('#searchResultsDropdown').change(function() {
        var selectedIsbn = $(this).val();
        // AJAX call to fetch book details and check availability
        $.ajax({
            type: 'POST',
            url: '../controllers/BookController.php',
            data: { action: 'getBookDetails', isbn: selectedIsbn },
            success: function(response) {
                var book = JSON.parse(response);
                // Populate modal with book details
                $('#modalTitle').text(book.title);
                $('#modalAuthor').text(book.author);
                // Check if book is available for borrowing
                if (book.available) {
                    $('#modalAvailability').text('Available for borrowing');
                    $('#borrowButton').show().data('isbn', selectedIsbn);
                } else {
                    $('#modalAvailability').text('Currently borrowed');
                    $('#borrowButton').hide();
                }
                // Display the modal
                $('#bookModal').modal('show');
            }
        });
    });

    // Handle borrow button click
    $('#borrowButton').click(function() {
        var isbn = $(this).data('isbn');
        // Implement borrowing logic here
        // You'll need another AJAX call or form submission to actually borrow the book
        alert('Borrowing book with ISBN: ' + isbn);
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
