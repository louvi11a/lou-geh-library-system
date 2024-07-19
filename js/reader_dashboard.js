$(document).ready(function() {
     // Function to populate autocomplete with search results
     function populateAutocomplete(books) {
        $('#searchQuery').autocomplete({
            source: books.map(function(book) {
                return {
                    label: book.title + ' by ' + book.author,
                    value: book.isbn,
                    title: book.title // Add title to the object
                };
            }),
            select: function(event, ui) {
                var selectedIsbn = ui.item.value;
                var selectedTitle = ui.item.title; // Get the selected title
                
                // Set the search input to the selected title
                $('#searchQuery').val(selectedTitle);
                
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
                        $('#modalPublisher').text(book.publisher);
                        $('#modalPublicationYear').text(book.publication_year);
                        $('#modalPages').text(book.number_of_pages);
                        // Display the modal
                        $('#bookModal').modal('show');
                    },
                    error: function(xhr, status, error) {
                        console.error("AJAX error:", error);
                    }
                });

                return false; // Prevent default action (replacing the input value)
            }
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

    // Handle search input keyup for autocomplete
    $('#searchQuery').keyup(function() {
        var query = $(this).val().trim();
        if (query !== '') {
            $.ajax({
                type: 'POST',
                url: '../controllers/BookController.php',
                data: { action: 'searchBooks', query: query },
                success: function(response) {
                    try {
                        var books = JSON.parse(response);
                        if (books.length > 0) {
                            populateAutocomplete(books);
                        }
                    } catch (e) {
                        console.error("Parsing error:", e);
                        console.error("Response:", response);
                    }
                },
                error: function(xhr, status, error) {
                    console.error("AJAX error:", error);
                }
            });
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
