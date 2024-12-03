# Automated Assignment Evaluation (AAE) System

## Overview
The Automated Assignment Evaluation (AAE) system streamlines and enhances the grading process of student assignments using AI. It automatically evaluates assignments, generates similarity scores, and produces detailed reports. The system consists of a user-friendly web interface, a robust backend, and advanced AI evaluation modules.

## Installation

### Prerequisites
- Python 3.8+
- MySQL

### Step-by-Step Guide

1. **Create and Activate a Virtual Environment**
    ```
    python3 -m venv venv
    source venv/bin/activate   # On Windows: venv\Scripts\activate
    ```

2. **Install the Required Libraries**
    ```
    pip install -r requirements.txt
    ```


3. **Set Up the Database**
    - Ensure MySQL is installed and running.
    - Create a database named `aae`.
    - Update the `dbconfig` section in your application code with your MySQL credentials.

4. **Running the Application with WSGI**
    - Install `waitress` if you haven't already:
      ```
      pip install waitress
      ```
    - Create a file named `wsgi.py` in your project directory with the following content:
      ```python
      from your_application_module import app

      if __name__ == "__main__":
          from waitress import serve
          serve(app, host="0.0.0.0", port=8080)
      ```
    - Run the WSGI server:
      ```
      python wsgi.py
      ```

## Usage

### For Instructors:
- **Register and Log In**: Access the platform to upload assignment tasks and solutions.
- **View Submissions**: Check student submissions and generate evaluation reports.

### For Students:
- **Register and Log In**: Access the platform to download assignment tasks.
- **Submit Assignments**: Upload completed assignments and view evaluation reports.

## Contact
For any queries or support, please contact:
- Muhammad Salman Ahmad (20011519-011)
- Manahil Amjad (20011519-027)
- Laiba Younas (20011519-009)