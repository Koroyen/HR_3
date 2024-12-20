import sys
import pandas as pd
from sklearn.ensemble import RandomForestClassifier
import joblib
import mysql.connector

# Load pre-trained model
model = joblib.load('hiring_ai_model.pkl')

# Database connection to fetch applicant data
db = mysql.connector.connect(
    host="localhost",
    user="root",
    password="",
    database="db_login"
)

cursor = db.cursor()

# Get the applicant ID from the command-line argument (passed from PHP)
applicant_id = sys.argv[1]

# Fetch applicant data from the database
query = "SELECT age, sex, experience FROM hiring WHERE id = %s"
cursor.execute(query, (applicant_id,))
applicant = cursor.fetchone()

# Check if the applicant exists
if applicant:
    # Assuming the model needs age, sex, and experience as features
    features = pd.DataFrame({
        'age': [applicant[0]],         # age
        'sex': [applicant[1]],         # sex
        'experience': [applicant[2]],  # experience
        # Add other required features...
    })

    # Predict suitability score
    suitability_score = model.predict(features)[0]

    # Output the score (this will be captured by PHP)
    print(suitability_score)
else:
    print("Applicant not found")
