$(document).ready(function() {
    // Function to get the reader number
    function getReaderNumber() {
        console.log("Current window.readerNumber:", window.readerNumber);
        if (typeof window.readerNumber === 'undefined' || window.readerNumber === null) {
            console.error("Reader number is missing.");
            return null;
        }
        return window.readerNumber;
    }

    // Function to fetch and display borrowed books
    function fetchBorrowedBooks() {
        var readerNumber = getReaderNumber();
        if (!readerNumber) {
            console.error("Cannot fetch borrowed books without reader number.");
            return;
        }

        $.ajax({
            type: 'POST',
            url: '../controllers/BorrowController.php',
            data: { action: 'getBorrowedBooks', reader_number: readerNumber },
            dataType: 'json', // Expect JSON response
            success: function(response) {
                console.log("Response:", response);
                try {
                    if (Array.isArray(response)) {
                        response.forEach(function(book) {
                            $('#borrowedBooksList').append('<li>' + book.title + ' by ' + book.author + ' (ISBN: ' + book.isbn + ', Copy No: ' + book.copy_number + ', Return Date: ' + book.return_date + ')</li>');
                        });
                    } else {
                        console.error("Unexpected data format:", response);
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

    // Fetch and display reader profile information
    $.ajax({
        type: 'POST',
        url: '../controllers/UserController.php',
        data: { action: 'getReaderProfile' },
        success: function(response) {
            console.log("Profile Response:", response);
            if (!response) {
                console.error("Received empty response");
                return;
            }
            try {
                var profile = JSON.parse(response);
                console.log("Parsed Profile:", profile);
                if (profile.error) {
                    console.error("Error:", profile.error);
                } else {
                    $('#readerName').text(profile.first_name);
                    $('#familyName').text(profile.family_name);
                    $('#firstName').text(profile.first_name);
                    $('#city').text(profile.city);
                    $('#dob').text(profile.dob);
                    window.readerNumber = profile.reader_number;
                    console.log("Reader Number Set:", window.readerNumber);

                    // Call fetchBorrowedBooks after readerNumber is set
                    fetchBorrowedBooks();
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
            var selectedTitle = ui.item.title;
            
            $('#searchQuery').val(selectedTitle);
            
            $.ajax({
                type: 'POST',
                url: '../controllers/BookController.php',
                data: { action: 'getBookDetails', isbn: selectedIsbn },
                success: function(response) {
                    var book = JSON.parse(response);
                    showBookDetails(book); // Use the reusable function to show book details
                },
                error: function(xhr, status, error) {
                    console.error("AJAX error:", error);
                }
            });

            return false;
        }
    });
}

// Function to display book details in the modal
function showBookDetails(book) {
    $('#modalTitle').text(book.title);
    $('#modalAuthor').text(book.author);
    $('#modalPublisher').text(book.publisher);
    $('#modalPublicationYear').text(book.publication_year);
    $('#modalPages').text(book.number_of_pages);
    if (book.isAvailable) {
        $('#modalAvailability').text('Available for borrowing');
        $('#borrowButton').show().data('isbn', book.isbn);
    } else {
        $('#modalAvailability').text('Currently borrowed');
        $('#borrowButton').hide();
    }
    $('#bookModal').modal('show');
}


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

    $('#searchResultsDropdown').change(function() {
        var selectedIsbn = $(this).val();
        $.ajax({
            type: 'POST',
            url: '../controllers/BookController.php',
            data: { action: 'getBookDetails', isbn: selectedIsbn },
            success: function(response) {
                var book = JSON.parse(response);
                $('#modalTitle').text(book.title);
                $('#modalAuthor').text(book.author);
                if (book.available) {
                    $('#modalAvailability').text('Available for borrowing');
                    $('#borrowButton').show().data('isbn', selectedIsbn);
                } else {
                    $('#modalAvailability').text('Currently borrowed');
                    $('#borrowButton').hide();
                }
                $('#bookModal').modal('show');
            }
        });
    });

    $('#borrowButton').click(function() {
        var isbn = $(this).data('isbn');
        var readerNumber = getReaderNumber();

        $.ajax({
            type: 'POST',
            url: '../controllers/BorrowController.php',
            data: {
                action: 'borrowBook',
                reader_number: readerNumber,
                isbn: isbn
            },
            success: function(response) {
                var result = JSON.parse(response);
                if (result.status === 'success') {
                    alert(result.message);
                    $('#bookModal').modal('hide');
                } else {
                    alert(result.message);
                }
            },
            error: function(xhr, status, error) {
                console.error("AJAX error:", error);
            }
        });
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
