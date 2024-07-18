$(document).ready(function() {
    // General function to populate a dropdown
    function populateDropdown(data, dropdownId) {
        var dropdown = $(dropdownId);
        dropdown.empty(); // Clear existing options
        dropdown.append('<option value="">Select a category</option>'); // Add default option

        data.forEach(function(item) {
            dropdown.append('<option value="' + item.category_id + '">' + item.name + '</option>');
        });
    }

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

    // Function to populate parent categories dropdown in Add Category Modal
    function populateParentCategoriesDropdown() {
        var categories = JSON.parse(localStorage.getItem('categories'));
        
        if (categories) {
            populateDropdown(categories, '#parentCategory'); // Use the specific dropdown ID
        } else {
            fetchCategoriesFromServer(function(data) {
                populateDropdown(data, '#parentCategory');
            });
        }
    }

    // Function to populate book categories dropdown in Add Book Modal
    function populateBookCategoriesDropdown() {
        var categories = JSON.parse(localStorage.getItem('categories'));

        if (categories) {
            populateDropdown(categories, '#bookCategories'); // Use the specific dropdown ID
        } else {
            fetchCategoriesFromServer(function(data) {
                populateDropdown(data, '#bookCategories');
            });
        }
    }

    // Initialize dropdowns on page load
    populateParentCategoriesDropdown();
    populateBookCategoriesDropdown();

    // Optional: Re-populate when Add Category Modal is shown
    $('#addCategoryModal').on('show.bs.modal', function() {
        populateParentCategoriesDropdown();
    });



    // Populate categories when the modal is opened
    $('#addCategoryModal').on('show.bs.modal', function() {
        populateParentCategories();
    });


// Open modal for Add Book button
    $('#btnAddBook').click(function() {
        $('#addBookModal').css('display', 'block');
    });

    // Open modal for Add Publisher button
    $('#btnAddPublisher').click(function() {
        $('#addPublisherModal').css('display', 'block');
    });

    // Open modal for Add Category button
    $('#btnAddCategory').click(function() {
        $('#addCategoryModal').css('display', 'block');
    });

    // Open modal for Add Member button
    $('#btnAddMember').click(function() {
        $('#addMemberModal').css('display', 'block');
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
                publisher_id: $('#publisher_id').val(),
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
    $('#addMemberForm').submit(function(event) {
        event.preventDefault(); // Prevent default form submission

        // AJAX request to add member
        $.ajax({
            type: 'POST',
            url: '../controllers/AdminController.php',
            data: {
                action: 'addMember',
                username: $('#username').val(),
                password: $('#password').val(),
                family_name: $('#family_name').val(),
                first_name: $('#first_name').val(),
                city: $('#city').val(),
                dob: $('#dob').val()
            },
            success: function(response) {
                $('#addMemberMessage').html('<p style="color: green;">' + response + '</p>');
                // Optionally clear the form after successful submission
                $('#addMemberForm')[0].reset();
            },
            error: function() {
                $('#addMemberMessage').html('<p style="color: red;">Error adding member. Please try again later.</p>');
            }
        });
    });

    // Example: AJAX request to view readers
    $('#btnViewReaders').click(function() {
        $.ajax({
            type: 'POST',
            url: '../controllers/AdminController.php',
            data: { action: 'viewReaders' },
            dataType: 'json',
            success: function(response) {
                if (response.length > 0) {
                    var table = '<h3>Readers</h3><table><tr><th>Reader Number</th><th>Username</th><th>Family Name</th><th>First Name</th><th>City</th><th>Date of Birth</th></tr>';
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
                    table += '</table>';
                    $('#viewReadersModal .modal-content').html(table);
                    $('#viewReadersModal').css('display', 'block');
                } else {
                    $('#viewReadersModal .modal-content').html('<p>No readers found.</p>');
                    $('#viewReadersModal').css('display', 'block');
                }
            },
            error: function() {
                alert('Error fetching readers. Please try again later.');
            }
        });
    });
});
