import sys
import pandas as pd
import joblib
import pymysql
from sqlalchemy import create_engine
from sklearn.ensemble import RandomForestRegressor

# Ensure an applicant ID is passed as an argument
if len(sys.argv) < 2:
    print("Error: Please provide an applicant ID.")
    sys.exit(1)

# Fetch the applicant ID from the command-line arguments
hiring_id = sys.argv[1]

# Establish the database connection using SQLAlchemy (preferred method)
try:
    engine = create_engine('mysql+pymysql://hr3_mfinance:bgn^C8sHe8k*aPC6@localhost/hr3_mfinance')
    connection = engine.connect()  # Check if the connection is established
    print("Database connection successful.")
except Exception as e:
    print(f"Error connecting to the database: {str(e)}")
    sys.exit(1)

# Load the pre-trained model
try:
    model = joblib.load('hiring_ai_model.pkl')
    print("Model loaded successfully.")
except FileNotFoundError:
    print("Error: Model file 'hiring_ai_model.pkl' not found.")
    sys.exit(1)

# Load the trained columns used during model training
try:
    trained_columns = joblib.load('trained_columns.pkl')
    print("Trained columns loaded successfully.")
except FileNotFoundError:
    print("Error: Trained columns file 'trained_columns.pkl' not found.")
    sys.exit(1)

# Fetch applicant data based on the dynamic hiring ID
query = f"SELECT * FROM hiring WHERE id = {hiring_id}"
try:
    applicant_data = pd.read_sql(query, engine)
    print("Applicant data fetched successfully.")
except Exception as e:
    print(f"Error fetching applicant data: {str(e)}")
    sys.exit(1)

# Check if any data was returned for the hiring ID
if applicant_data.empty:
    print(f"No data found for hiring ID: {hiring_id}")
    sys.exit(1)

# Convert 'Age', 'experience_years', 'experience_months' to numeric if not already
applicant_data['Age'] = pd.to_numeric(applicant_data['Age'], errors='coerce')
applicant_data['experience_years'] = pd.to_numeric(applicant_data['experience_years'].fillna(0), errors='coerce')
applicant_data['experience_months'] = pd.to_numeric(applicant_data['experience_months'].fillna(0), errors='coerce')

# Fetch education and experience data
education = applicant_data['education'].values[0] if pd.notna(applicant_data['education'].values[0]) else 'None'
experience_years = applicant_data['experience_years'].values[0]
experience_months = applicant_data['experience_months'].values[0]

# Points for education (preferred education vs other)
preferred_education = ['University of the Philippines Diliman', 'Ateneo de Manila University', 'De La Salle University', 'University of Santo Tomas', 'Polytechnic University of the Philippines']

# Adjust points based on education
if education in preferred_education:
    education_points = 1.0  # Full points for preferred institutions
elif education == 'Other':
    education_points = 0.5  # Less points for 'Other' institutions
elif education == 'None':  # Handle missing or blank education
    education_points = 0.0  # No points for missing education
else:
    education_points = 0.7  # Middle points for non-preferred but known institutions

# Points based on experience
if experience_years >= 2:
    experience_points = 1.5  # Higher points for 2 or more years of experience
elif experience_years >= 1 and experience_years < 2:
    experience_points = 1.2  # Moderate points for 1-2 years
elif experience_years < 1 and experience_months >= 5:
    experience_points = 1.0  # Lower points for 5-11 months
else:
    experience_points = 0.5  # Minimal points for less than 5 months

# Combine experience years and months points (max 1.5 for experience)
total_experience_points = min(experience_points, 1.5)

# Debugging: Print the education and experience points
print(f"Education Points: {education_points}")
print(f"Total Experience Points: {total_experience_points}")

# Sum of points, ensuring the maximum score is 3.00
total_points = education_points + total_experience_points

# Debugging: Print the total points
print(f"Total Points: {total_points:.2f}")

# Output the final suitability score for display in PHP
print(f"{total_points:.2f}")

print(f"Predicting suitability for hiring ID: {hiring_id}")
