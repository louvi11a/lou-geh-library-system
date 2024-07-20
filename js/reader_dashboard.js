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
        dataType: 'json',
        success: function(response) {
            console.log("Response:", response);
            $('#borrowedBooksList').empty(); // Clear the list before appending new items
            try {
                if (Array.isArray(response)) {
                    response.forEach(function(book) {
                        $('#borrowedBooksList').append(
                            '<li>' + book.title + ' by ' + book.author + ' (ISBN: ' + book.isbn + ', Copy No: ' + book.copy_number + ', Return Date: ' + book.return_date + ') ' +
                            '<button class="returnButton" data-borrow-id="' + book.borrow_id + '">Return</button>' +
                            '</li>'
                        );
                    });

                    // Attach click event for return buttons
                    $('.returnButton').on('click', function() {
                        var borrowId = $(this).data('borrow-id');
                        console.log("Attempting to return book with borrowId:", borrowId); // Debugging
                        returnBook(borrowId);
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


// Function to handle book return
function returnBook(borrowId) {
    console.log("Attempting to return book with borrowId:", borrowId); // Log borrowId for debugging
    $.ajax({
        type: 'POST',
        url: '../controllers/BorrowController.php',
        data: { action: 'returnBook', borrow_id: borrowId },
        dataType: 'json',
        success: function(response) {
            console.log("Return book response:", response); // Log response for debugging
            if (response.status === 'success') {
                alert('Book returned successfully.');
                window.location.reload(); // Reload the page after successful return
            } else {
                alert('Error: ' + response.message);
            }
        },
        error: function(xhr, status, error) {
            console.error("AJAX error:", error);
            console.error("Response text:", xhr.responseText); // Log response text for debugging
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
                    fetchBorrowHistory();

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

    // Function to fetch and display borrow history
function fetchBorrowHistory() {
    var readerNumber = getReaderNumber();
    if (!readerNumber) {
        console.error("Cannot fetch borrow history without reader number.");
        return;
    }
    $.ajax({
        type: 'POST',
        url: '../controllers/BorrowController.php',
        data: { action: 'getBorrowHistory', reader_number: readerNumber },
        dataType: 'json',
        success: function(response) {
            console.log("Borrow History Response:", response);
            $('#borrowHistoryList').empty(); // Clear the list before appending new items
            try {
                if (Array.isArray(response)) {
                    response.forEach(function(borrow) {
                        $('#borrowHistoryList').append(
                            '<li>' + borrow.title + ' by ' + borrow.author + ' (ISBN: ' + borrow.isbn + ', Copy No: ' + borrow.copy_number + ', Borrow Date: ' + borrow.borrow_date + ', Return Date: ' + (borrow.return_date || 'Not Returned') + ')</li>'
                        );
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
    

    
// Function to display book details in the modal
function showBookDetails(book) {
    console.log("Book Object:", book); // Log the entire book object
    console.log("Book ISBN:", book.isbn); // Log ISBN for debugging
    $('#modalTitle').text(book.title);
    $('#modalAuthor').text(book.author);
    $('#modalPublisher').text(book.publisher);
    $('#modalPublicationYear').text(book.publication_year);
    $('#modalPages').text(book.number_of_pages);
    $('#modalISBN').text(book.isbn); // Ensure ISBN is set in the modal

    // Fetch the number of copies
    $.ajax({
        url: 'path/to/your/controller/getCopiesCount.php', // Adjust path to your endpoint
        type: 'GET',
        data: { isbn: book.isbn },
        success: function(response) {
            var data = JSON.parse(response);
            $('#bookCopiesCount').text(data.copy_count);
        },
        error: function() {
            $('#bookCopiesCount').text('Error fetching data');
        }
    });
    
    if (book.isAvailable) {
        $('#modalAvailability').text('Available for borrowing');
        $('#borrowButton').show().data('isbn', book.isbn);
    } else {
        $('#modalAvailability').text('Currently borrowed');
        $('#borrowButton').hide();
    }

    $('#bookModal').modal('show');
}



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

    $('#borrowButton').on('click', function() {
        var readerNumber = getReaderNumber();
        var isbn = $('#modalISBN').text().trim(); // Ensure ISBN is trimmed of whitespace
    
        console.log("Reader Number:", readerNumber);
        console.log("ISBN from Modal:", isbn);
    
        if (!readerNumber || !isbn) {
            console.error("Reader number or ISBN is missing.");
            return;
        }
    
        $.ajax({
            type: 'POST',
            url: '../controllers/BorrowController.php',
            data: {
                action: 'borrowBook',
                reader_number: readerNumber,
                isbn: isbn
            },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    alert(response.message);
                    $('#bookModal').modal('hide'); // Hide the modal on success
                    location.reload(); // Refresh the page to display the latest updates

                } else {
                    console.error('Error:', response.message);
                    alert('Error: ' + response.message);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error('AJAX error:', textStatus, errorThrown);
                console.error('Response:', jqXHR.responseText);
                alert('Error: ' + jqXHR.responseText); // Display the response for debugging
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
