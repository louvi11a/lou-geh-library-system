$(document).ready(function() {
    // General function to populate a dropdown
    function populateDropdown(data, dropdownId, valueField, textField) {
        console.log('Populating dropdown with data:', data);

        var dropdown = $(dropdownId);
        dropdown.empty(); // Clear existing options
        dropdown.append('<option value="">Select an option</option>'); // Add default option

        if (Array.isArray(data)) {
            data.forEach(function(item) {
                var value = item[valueField];
                var text = item[textField];
                dropdown.append('<option value="' + value + '">' + text + '</option>');
            });
        } else {
            console.error('Data is not an array:', data);
        }
    }

    
// Function to display all books
function displayAllBooks() {
    console.log("Sending AJAX request to fetch all books");

    $.ajax({
        type: 'POST',
        url: '../controllers/BookController.php',
        data: { action: 'getAllBooks' },
        dataType: 'json',
        success: function(response) {
            console.log("AJAX request successful", response);

            if (response.status === 'success') {
                if (Array.isArray(response.data) && response.data.length > 0) {
                    var table = '<h1>Books List</h1><table class="table table-striped table-bordered"><thead><tr><th>ISBN</th><th>Title</th><th>Author</th><th>Publication Year</th><th>Number of Pages</th><th>Publisher</th><th>Copies</th></tr></thead><tbody>';
                    response.data.forEach(function(book) {
                        table += '<tr>';
                        table += '<td>' + book.isbn + '</td>';
                        table += '<td>' + book.title + '</td>';
                        table += '<td>' + book.author + '</td>';
                        table += '<td>' + book.publication_year + '</td>';
                        table += '<td>' + book.number_of_pages + '</td>';
                        table += '<td>' + book.publisher + '</td>';
                        table += '<td>' + book.total_copies + '</td>';
                        table += '</tr>';
                    });
                    table += '</tbody></table>';

                    $('#booksTableContainer').html(table);
                    $('#booksTableMessage').addClass('d-none'); // Hide the message if books are found
                } else {
                    console.log("No books found");
                    $('#booksTableContainer').html('');
                    $('#booksTableMessage').removeClass('d-none').text('No books found.');
                }
                $('#viewBooksModal').modal('show');
            } else {
                console.error('Error fetching books:', response.message);
                $('#booksTableContainer').html('');
                $('#booksTableMessage').removeClass('d-none').text('Error fetching books.');
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', status, error);
            $('#booksTableContainer').html('');
            $('#booksTableMessage').removeClass('d-none').text('Error fetching books.');
        }
    });
}

    // Initialize event handlers and other functions
    $('#btnViewBooks').click(function() {
        displayAllBooks();
    });

    
    
    // Function to fetch categories from the server
    function fetchCategoriesFromServer(callback) {
        $.ajax({
            url: '../controllers/getCategories.php',
            type: 'GET',
            success: function(response) {
                localStorage.setItem('categories', JSON.stringify(response));
                callback(response); // Pass data to callback function
            },
            error: function() {
                console.error('Error fetching categories.');
            }
        });
    }

    function fetchPublishersFromServer(callback) {
        $.ajax({
            type: 'POST',
            url: '../controllers/PublisherController.php',
            data: { action: 'getAllPublishers' },
            dataType: 'json',
            success: function(response) {
                console.log("Response from fetchPublishersFromServer:", response); // Debug statement
    
                if (response.status === 'success') {
                    localStorage.setItem('publishers', JSON.stringify(response.data));
                    callback(response.data);
                } else {
                    console.error('Error fetching publishers:', response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', status, error);
                console.error('Response Text:', xhr.responseText);
            }
        });
    }
    
    

    // Function to display borrowed books
    function displayBorrowedBooks() {
        $.ajax({
            type: 'POST',
            url: '../controllers/AdminController.php',
            data: { action: 'viewBorrowedBooks' },
            dataType: 'json',
            success: function(response) {
                if (response.length > 0) {
                    var table = '<h1>Borrow History</h1><table class="table"><thead><tr><th>Borrow ID</th><th>Book Title</th><th>Reader Name</th><th>Borrow Date</th><th>Return Date</th></tr></thead><tbody>';
                    response.forEach(function(borrow) {
                        table += '<tr>';
                        table += '<td>' + borrow.borrow_id + '</td>';
                        table += '<td>' + borrow.book_title + '</td>';
                        table += '<td>' + borrow.reader_name + '</td>';
                        table += '<td>' + borrow.borrow_date + '</td>';
                        table += '<td>' + (borrow.return_date ? borrow.return_date : 'Not Returned') + '</td>';
                        table += '</tr>';
                    });
                    table += '</tbody></table>';

                    $('#borrowedBooksTableContainer').html(table);
                    $('#borrowedBooksTableMessage').addClass('d-none');
                } else {
                    $('#borrowedBooksTableContainer').html('');
                    $('#borrowedBooksTableMessage').removeClass('d-none').text('No borrowed books found.');
                }
                $('#viewBorrowedBooksModal').modal('show');
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', status, error);
                console.error('Response Text:', xhr.responseText);
                alert('Error fetching borrowed books. Please try again later.');
            }
        });
    }


    // Function to populate parent categories dropdown in Add Category Modal
    function populateParentCategoriesDropdown() {
        var categories = JSON.parse(localStorage.getItem('categories'));
    
        if (categories) {
            populateDropdown(categories, '#parentCategory', 'id', 'name'); // Adjust field names as needed
        } else {
            fetchCategoriesFromServer(function(data) {
                populateDropdown(data, '#parentCategory', 'id', 'name');
            });
        }
    }
    
    

    // Function to populate book categories dropdown in Add Book Modal
    function populateBookCategoriesDropdown() {
        var categories = JSON.parse(localStorage.getItem('categories'));
    
        if (categories) {
            populateDropdown(categories, '#bookCategories', 'id', 'name'); // Adjust field names as needed
        } else {
            fetchCategoriesFromServer(function(data) {
                populateDropdown(data, '#bookCategories', 'id', 'name');
            });
        }
    }
    

    function populatePublishersDropdown() {
        var publishers = JSON.parse(localStorage.getItem('publishers'));
    
        console.log('Publishers from localStorage:', publishers); // Debug statement
    
        if (publishers) {
            populateDropdown(publishers, '#publisher_id', 'publisher_id', 'name'); // Adjust field names as needed
        } else {
            fetchPublishersFromServer(function(data) {
                console.log('Fetched publishers:', data); // Debug statement
                populateDropdown(data, '#publisher_id', 'publisher_id', 'name');
            });
        }
    }
    
    

    // Initialize dropdowns on page load
    populateParentCategoriesDropdown();
    populateBookCategoriesDropdown();
    populatePublishersDropdown();

    // Optional: Re-populate when Add Category Modal is shown
    $('#addCategoryModal').on('show.bs.modal', function() {
        populateParentCategoriesDropdown();
    });

    // Open modal for Add Book button
    $('#btnViewBooks').click(function() {
        $('#viewBooksModal').css('display', 'block');
    });

// Open modal for Add Book button
    $('#btnAddBook').click(function() {
        $('#addBookModal').css('display', 'block');
    });

    // Open modal for Add Publisher button
    $('#btnAddPublisher').click(function() {
        $('#addPublisherModal').css('display', 'block');
    });

        // Trigger display of borrowed books when button is clicked
        $('#btnViewBorrowedBooks').click(function() {
            displayBorrowedBooks();
        });

        
    // Trigger display of all books when button is clicked
    $('#btnViewBooks').click(function() {
        displayAllBooks();

    });

    // Open modal for Add Category button
    $('#btnAddCategory').click(function() {
        $('#addCategoryModal').css('display', 'block');
    });

    // Open modal for Add Member button
    $('#btnAddUser').click(function() {
        $('#addUserModal').css('display', 'block');
    });

    // Close modal when clicking on close button
    $('.close').click(function() {
        $('.modal').css('display', 'none');
    });

    // Close modal when clicking outside of it
    $(window).click(function(event) {
        if (event.target.className === 'modal') {
            $('.modal').css('display', 'none');
        }
    });


    // AJAX request to add book
    $('#addBookForm').submit(function(event) {
        event.preventDefault(); // Prevent default form submission

        // Collect selected categories
        var selectedCategories = [];
        $('input[name="category[]"]:checked').each(function() {
            selectedCategories.push($(this).val());
        });
        var publisherId = $('#publisher_id').val(); // Get the selected publisher ID

        // AJAX request to add book
        $.ajax({
            type: 'POST',
            url: '../controllers/BookController.php',
            data: {
                action: 'addBook',  // Ensure this matches the case in PHP switch statement
                isbn: $('#isbn').val(),
                title: $('#title').val(),
                author: $('#author').val(),
                publication_year: $('#publication_year').val(),
                number_of_pages: $('#number_of_pages').val(),
                publisher_id: publisherId, // Send the selected publisher ID
                category: selectedCategories  // Assuming selectedCategories is correctly defined
            },
            success: function(response) {
                $('#addBookMessage').html('<p style="color: green;">' + response + '</p>');
                // closeModal();
                $('#addBookForm')[0].reset();  // Optionally reset form fields
            },
            error: function() {
                $('#addBookMessage').html('<p style="color: red;">Error adding book. Please try again later.</p>');
            }
        });
        
    });

    // AJAX request to add publisher
    $('#addPublisherForm').submit(function(event) {
        event.preventDefault(); // Prevent default form submission

        // AJAX request to add publisher
        $.ajax({
            type: 'POST',
            url: '../controllers/AdminController.php',
            data: {
                action: 'addPublisher',
                name: $('#name').val(),
                location: $('#location').val()
            },
            success: function(response) {
                $('#addPublisherMessage').html('<p style="color: green;">' + response + '</p>');
                // Optionally clear the form after successful submission
                $('#addPublisherForm')[0].reset();
            },
            error: function() {
                $('#addPublisherMessage').html('<p style="color: red;">Error adding publisher. Please try again later.</p>');
            }
        });
    });

    // AJAX request to add category
    $('#addCategoryForm').submit(function(event) {
        event.preventDefault(); // Prevent default form submission

        // AJAX request to add category
        $.ajax({
            type: 'POST',
            url: '../controllers/CategoryController.php',
            data: {
                action: 'addCategory',
                categoryName: $('#categoryName').val(),
                parentCategory: $('#parentCategory').val()
            },
            success: function(response) {
                $('#addCategoryMessage').html('<p style="color: green;">' + response + '</p>');
                $('#addCategoryForm')[0].reset();  // Optionally reset form fields
            },
            error: function() {
                $('#addCategoryMessage').html('<p style="color: red;">Error adding category. Please try again later.</p>');
            }
        });
    });

    // AJAX request to add member
    $('#addUserForm').submit(function(event) {
        event.preventDefault(); // Prevent default form submission

        // AJAX request to add member
        $.ajax({
            type: 'POST',
            url: '../controllers/AdminController.php',
            data: {
                action: 'addUser',
                username: $('#username').val(),
                password: $('#password').val(),
                family_name: $('#family_name').val(),
                first_name: $('#first_name').val(),
                city: $('#city').val(),
                dob: $('#dob').val()
            },
            success: function(response) {
                $('#addUserMessage').html('<p style="color: green;">' + response + '</p>');
                // Optionally clear the form after successful submission
                $('#adduserForm')[0].reset();
            },
            error: function() {
                $('#addUserMessage').html('<p style="color: red;">Error adding member. Please try again later.</p>');
            }
        });
    });

// Close modal when clicking on close button
$('.close').click(function() {
    $(this).closest('.modal').css('display', 'none');
});

// AJAX request to view readers
$('#btnViewReaders').click(function() {
    $.ajax({
        type: 'POST',
        url: '../controllers/AdminController.php',
        data: { action: 'viewReaders' },
        dataType: 'json',
        success: function(response) {
            if (response.length > 0) {
                var table = '<h1>Readers List</h1><table class="table"><thead><tr><th>Reader Number</th><th>Username</th><th>Family Name</th><th>First Name</th><th>City</th><th>Date of Birth</th></tr></thead><tbody>';
                for (var i = 0; i < response.length; i++) {
                    table += '<tr>';
                    table += '<td>' + response[i].reader_number + '</td>';
                    table += '<td>' + response[i].username + '</td>';
                    table += '<td>' + response[i].family_name + '</td>';
                    table += '<td>' + response[i].first_name + '</td>';
                    table += '<td>' + response[i].city + '</td>';
                    table += '<td>' + response[i].dob + '</td>';
                    table += '</tr>';
                }
                table += '</tbody></table>';

                $('#readersTableContainer').html(table);
                $('#readersTableMessage').html('');
            } else {
                $('#readersTableContainer').html('');
                $('#readersTableMessage').html('<p>No readers found.</p>');
            }
            $('#viewReadersModal').modal('show');
        },
        error: function() {
            alert('Error fetching readers. Please try again later.');
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
