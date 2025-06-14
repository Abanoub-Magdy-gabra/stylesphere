# Style Quiz Backend

This is the PHP/MySQL backend for the Style Quiz application.

## Prerequisites

- PHP 7.4 or higher
- MySQL 5.7 or higher (or MariaDB 10.2 or higher)
- Web server (Apache/Nginx) with mod_rewrite enabled

## Setup Instructions

1. **Database Setup**
   - Create a new MySQL database named `style_quiz`
   - Import the database schema:
     ```
     mysql -u [username] -p style_quiz < database/schema.sql
     ```
   - Update the database credentials in `config/database.php` if needed

2. **Web Server Configuration**
   - Point your web server's document root to the `backend` directory
   - Make sure mod_rewrite is enabled for clean URLs

3. **API Endpoints**

   - `GET /api/get_questions` - Fetch all quiz questions
   - `POST /api/submit_quiz` - Submit quiz answers
     
   Example request to submit quiz answers:
   ```json
   {
     "user_id": 1,
     "answers": {
       "question_1": "answer_1",
       "question_2": "answer_2"
     }
   }
   ```

## Development

- The API follows RESTful conventions
- All responses are in JSON format
- Error responses include a status code and error message

## Security Notes

- In production, consider:
  - Moving database credentials to environment variables
  - Implementing proper authentication
  - Setting up HTTPS
  - Adding rate limiting
  - Validating and sanitizing all inputs

## License

MIT
