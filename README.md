# Lou Geh Library System

## Setup Guide

### Prerequisites

- PHP (>= 7.4)
- MySQL
- Apache or any web server

### Installation

1. Clone the repository:

    ```bash
    git clone https://github.com/louvi11a/lou-geh-library-system.git
    ```

2. Navigate to the project directory:

    ```bash
    cd lou-geh-library-system
    ```

3. Set up the database:

    - Open MySQL command line or any MySQL client
    - Run the SQL script located in `database/schema.sql`

4. Configure the database connection:

    - Open `configs/db.php`
    - Set your database credentials (servername, username, password, dbname)

5. Start the server:

    - If using Apache, place the project directory inside the `htdocs` folder.
    - If using PHP built-in server, run the following command:

    ```bash
    php -S localhost:8000
    ```

6. Access the application:

    Open your web browser and go to `http://localhost:8000`


## License

This project is licensed under the MIT License.
