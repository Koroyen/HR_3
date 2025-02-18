import mysql.connector
import pandas as pd
from sklearn.model_selection import train_test_split
from sklearn.ensemble import RandomForestRegressor
from sklearn.metrics import mean_squared_error
import pickle
import joblib
from sqlalchemy import create_engine

# Connect to the database
conn = mysql.connector.connect(
    host="localhost",
    user="hr3_mfinance",
    password="bgn^C8sHe8k*aPC6",
    database="hr3_mfinance"
)

# SQLAlchemy connection string
engine = create_engine('mysql+mysqlconnector://root:@localhost/db_login')

# SQL query to fetch data from the 'hiring' table
query = """
SELECT Age, sex, job_position, experience_years, experience_months, education, otherEducation, status, suitability_score
FROM hiring
WHERE job_position IN ('Human Resource assistant', 'Human Resource specialist', 'Human Resource coordinator')
"""

# Fetch data from the 'hiring' table using SQLAlchemy connection
df = pd.read_sql(query, engine)

# Add the preferred education list
preferred_education = [
    'University of the Philippines Diliman', 
    'Ateneo de Manila University', 
    'De La Salle University', 
    'University of Santo Tomas', 
    'Polytechnic University of the Philippines'
]

# Assign points for preferred education, other education, and otherEducation field
df['education_points'] = df.apply(
    lambda row: 1.0 if row['education'] in preferred_education 
                else 0.7 if row['education'] == 'Other' and row['otherEducation'] 
                else 0.5, 
    axis=1
)

# Add experience points based on years and months
def assign_experience_points(years, months):
    if years >= 2:
        return 1.5  # Higher points for 2+ years
    elif years >= 1:
        return 1.2  # Moderate points for 1-2 years
    elif months >= 5 and years < 1:
        return 1.0  # Points for 5-11 months
    else:
        return 0.5  # Minimal points for less than 5 months

df['experience_points'] = df.apply(lambda row: assign_experience_points(row['experience_years'], row['experience_months']), axis=1)

# Drop any rows with missing values
df = df.dropna()

# Encode categorical variables (e.g., 'sex', 'job_position')
df = pd.get_dummies(df, columns=['sex', 'job_position'])

# Separate features (X) and target (y)
X = df.drop(columns=['suitability_score', 'status', 'education', 'otherEducation'])  # Drop target and irrelevant columns
y = df['suitability_score']  # Use 'suitability_score' as the target

# Split the data into training and testing sets
X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.2, random_state=42)

# Train the model using RandomForestRegressor
model = RandomForestRegressor(random_state=42)
model.fit(X_train, y_train)

# Make predictions
y_pred = model.predict(X_test)

# Evaluate the model using Mean Squared Error (MSE)
mse = mean_squared_error(y_test, y_pred)
print(f"Mean Squared Error: {mse}")

# Save the trained model to a file
with open('hiring_ai_model.pkl', 'wb') as file:
    pickle.dump(model, file)

# Save the columns used for training
joblib.dump(X_train.columns, 'trained_columns.pkl')

# Close the database connection
conn.close()
