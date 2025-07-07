# Customer SPA

This is a Single Page Application (SPA) built with vanilla JavaScript, TypeScript, and PHP to manage customer data. The application provides a user interface to view customer details, contacts, addresses, and activity logs, with a backend API powered by PHP and a MySQL database.

## Features
- View a list of customers with search functionality.
- Display detailed customer information, including contacts, addresses, and activity.
- Responsive design with tabbed navigation.
- RESTful API endpoints for CRUD operations.

## Prerequisites
- PHP 7.4 or higher with PDO extension.
- Node.js and npm for front-end build.
- MySQL database with the required schema.

## Installation
1. Clone the repository:
   ```bash
   git clone https://github.com/your-username/customer-spa.git
   cd customer-spa
   ```
2. Install dependencies:
   ```bash
   npm install
   ```
3. Set up the database:
   - Create a MySQL database and import the schema from `database/schema.sql`.
   - Update `server/core/config.php` with your database credentials.
4. Build the project:
   ```bash
   npm run build
   ```
5. Start the PHP development server:
   ```bash
   php -S localhost:8000 -t dist/
   ```
6. Open `http://localhost:8000` in your browser.

## Project Structure
- `src/`: Front-end source code (TypeScript, HTML, CSS).
- `server/`: Backend PHP code, including controllers and core logic.
- `dist/`: Compiled output for production.
- `database/`: SQL schema and data.

## Development
- Run `npm run watch` to automatically rebuild on file changes.
- Use `npm run build` for a production build.

## New Section: Understanding GenericCrudController.php

The `GenericCrudController.php` file serves as a base class for handling CRUD (Create, Read, Update, Delete) operations across different database tables in a consistent manner. It provides a reusable foundation that can be extended to manage any table by implementing specific table-related logic in child controllers. This approach reduces code duplication and ensures uniform API response formats (`{success: true, data: ...}` or `{success: false, error: ...}`).

### How It Works
- **Core Functionality**: The class handles HTTP methods (GET, POST, PUT, DELETE) and enforces a standard response structure. It includes methods for database connection, query preparation, parameter binding, and error handling.
- **Configuration**: Each child controller must define `table`, `primaryKey`, `allowedFields`, `booleanFields`, and `defaultOrder` in the `initialize` method.
- **Extensibility**: Child classes override `handleGet`, `handlePost`, `handlePut`, `handleDelete`, and optional methods like `getSearchConditions` and `validateRequiredFields` to tailor behavior to specific tables.
- **Error Handling**: Exceptions are caught and logged, with debug information available when `debug_mode` is enabled in the config. PHP logging is now enabled by default, writing errors and debug output to `dist/logs/php_errors.log`.
- **Logging**: Errors and debug information are written to `dist/logs/php_errors.log` if the directory is writable. Ensure the `dist/logs/` directory exists and has appropriate permissions (e.g., `chmod -R 775 dist/logs` and `chown -R your-username:your-group dist/logs`).

### Example: Extending for a New Table (Demographics)
To add a new table (e.g., `demographics`) to store customer demographic data, create a new controller by extending `GenericCrudController.php`. Here's an example:

1. Create a new file `server/api/DemographicsController.php`:
   ```php
   <?php
   // server/api/DemographicsController.php
   declare(strict_types=1);

   require_once __DIR__ . '/../core/GenericCrudController.php';

   class DemographicsController extends GenericCrudController {
       protected function initialize(): void {
           $this->table = 'demographics';
           $this->primaryKey = 'demographic_id';
           $this->allowedFields = [
               'demographic_id', 'customer_id', 'age_group', 'gender', 'income_level',
               'created_at', 'updated_at'
           ];
           $this->booleanFields = [];
       }

       protected function handleGet(array $input): array {
           if (empty($input['customer_id'])) {
               throw new RuntimeException('customer_id parameter is required', 400);
           }

           $conditions = ["customer_id = :customer_id"];
           $params = [':customer_id' => $input['customer_id']];

           $query = "SELECT * FROM {$this->table} WHERE " . implode(' AND ', $conditions);
           $query .= " ORDER BY created_at DESC";

           if ($this->config['debug_mode']) {
               error_log("[DEBUG] Demographics query: {$query}");
               error_log("[DEBUG] Demographics params: " . json_encode($params));
           }

           try {
               $stmt = $this->db->prepare($query);
               foreach ($params as $key => $value) {
                   $type = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
                   $stmt->bindValue($key, $value, $type);
               }
               $stmt->execute();
               return $stmt->fetchAll(PDO::FETCH_ASSOC);
           } catch (PDOException $e) {
               $this->handleDatabaseError($e, $query, $params);
               throw new RuntimeException('Database query failed', 500);
           }
       }

       protected function validateRequiredFields(array $data): void {
           $required = ['customer_id', 'age_group', 'gender'];
           $missing = array_diff($required, array_keys($data));
           
           if (!empty($missing)) {
               throw new RuntimeException('Missing required fields: ' . implode(', ', $missing), 400);
           }
       }
   }

   (new DemographicsController())->handleRequest();
   ```
2. Update the database schema (`database/schema.sql`) with the following SQL commands to create the `demographics` table:
   ```sql
   CREATE TABLE demographics (
       demographic_id INT AUTO_INCREMENT PRIMARY KEY,
       customer_id INT NOT NULL,
       age_group VARCHAR(50),
       gender VARCHAR(20),
       income_level VARCHAR(50),
       created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
       updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
       FOREIGN KEY (customer_id) REFERENCES customers(customer_id)
   );

   -- Optional: Insert sample data
   INSERT INTO demographics (customer_id, age_group, gender, income_level) VALUES
   (1, '25-34', 'Male', '50k-75k'),
   (2, '35-44', 'Female', '75k-100k');
   ```
3. Update the front-end (e.g., `src/ts/customerService.ts` and `src/ts/ui.ts`) to fetch and display demographic data if needed.

This example creates a new endpoint (`/api/DemographicsController.php?customer_id=X`) to retrieve demographic information for a given customer.

## Git Commands
To commit and push your changes to the repository, use the following commands:

```bash
# Stage all changed files
git add .

# Commit the changes with a descriptive message
git commit -m "Add GenericCrudController documentation, PHP logging note, DemographicsController, and SQL schema"

# Push to the remote repository
git push origin main
```

Replace `main` with your branch name if you're using a different one (e.g., `master` or a feature branch).

## Contributing
Feel free to submit issues or pull requests. Ensure you follow the project structure and test your changes thoroughly.

## License
This project is licensed under the MIT License.
```

### Instructions
1. **Update README**: Replace the contents of your `README.md` with the provided text.
2. **Apply SQL Schema**: Add the SQL commands to `database/schema.sql` or run them manually in your MySQL database to create the `demographics` table.
3. **Create New Controller (Optional)**: If you want to implement the `demographics` feature, create `server/api/DemographicsController.php` with the provided code.
4. **Run Git Commands**: Execute the Git commands in your project directory to commit and push the changes.
5. **Verify**:
   - After pushing, check the repository online to ensure the README updates are reflected.
   - Test the application and check `dist/logs/php_errors.log` for any debug output if issues arise.
