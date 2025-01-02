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
engine = create_engine('mysql+pymysql://hr3_mfinance:bgn^C8sHe8k*aPC6@hr3.microfinance-solution.com/db_login')

# Load the pre-trained model
try:
    model = joblib.load('hiring_ai_model.pkl')
except FileNotFoundError:
    print("Error: Model file 'hiring_ai_model.pkl' not found.")
    sys.exit(1)

# Load the trained columns used during model training
try:
    trained_columns = joblib.load('trained_columns.pkl')
except FileNotFoundError:
    print("Error: Trained columns file 'trained_columns.pkl' not found.")
    sys.exit(1)

# Fetch applicant data based on the dynamic hiring ID
query = f"SELECT * FROM hiring WHERE id = {hiring_id}"
applicant_data = pd.read_sql(query, engine)

# Check if any data was returned for the hiring ID
if applicant_data.empty:
    print(f"No data found for hiring ID: {hiring_id}")
    sys.exit(1)

# Convert 'Age' and 'experience' to numeric if not already
applicant_data['Age'] = pd.to_numeric(applicant_data['Age'], errors='coerce')
applicant_data['experience'] = pd.to_numeric(applicant_data['experience'].fillna(0), errors='coerce')

# Debugging: Print the applicant data
print(applicant_data)

# Drop irrelevant columns and ensure only columns used in training remain
try:
    X_new = applicant_data.drop(columns=['id', 'city_id', 'education_match', 'experience_match', 'sex', 'job_position'])
except KeyError as e:
    print(f"Error: Missing column in data: {e}")
    sys.exit(1)

# Debugging: Print the data after dropping irrelevant columns
print("Data after dropping irrelevant columns:")
print(X_new)

# Apply custom logic to boost scores for relevant experience and job position
experience = applicant_data['experience'].values[0]
job_position = applicant_data['job_position'].values[0]

# Give higher weight to job experience between 1 to 2 years
if experience >= 1 and experience <= 2:
    experience_boost = 0.2  # 20% boost to the score
elif experience > 2:
    experience_boost = 0.3  # Slightly higher boost for more experience
else:
    experience_boost = 0  # No boost for 0 years of experience

# Debugging: Print experience boost
print(f"Experience Boost: {experience_boost}")

# Boost score further if the job position matches
if job_position in ['Human Resource assistant', 'Human Resource specialist', 'Human Resource coordinator']:
    job_position_boost = 0.2  # Another 20% boost if job position matches
else:
    job_position_boost = 0

# Debugging: Print job position boost
print(f"Job Position Boost: {job_position_boost}")

# Add missing columns (if any) that were used during training
for col in trained_columns:
    if col not in X_new.columns:
        X_new[col] = 0  # Add missing columns and set them to 0

# Drop any extra columns that were not used during training
X_new = X_new[trained_columns]

# Ensure all columns are numeric
X_new = X_new.apply(pd.to_numeric, errors='coerce')

# Debugging: Print final data being passed to the model
print("Final data passed to model:")
print(X_new)

# Predict the suitability score using the pre-trained model
try:
    prediction = model.predict(X_new)
    base_score = prediction[0]

    # Debugging: Print base score from the model
    print(f"Base model prediction: {base_score}")

    # Apply the boosts
    boosted_score = base_score + experience_boost + job_position_boost

    # Ensure the score doesn't exceed 1.0 (or any other maximum value you want)
    final_score = min(boosted_score, 1.0)

    # Format the final prediction to 3 decimal places
    formatted_prediction = round(final_score, 3)

    # Output the final prediction score
    print(formatted_prediction)

except Exception as e:
    print(f"Error during prediction: {str(e)}")
    sys.exit(1)

# Close the database connection
engine.dispose()
