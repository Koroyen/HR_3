import mysql.connector
import pandas as pd
from sklearn.model_selection import train_test_split
from sklearn.ensemble import RandomForestRegressor  # Import the regressor instead of classifier
from sklearn.metrics import mean_squared_error
import pickle
import joblib

# Connect to the database
conn = mysql.connector.connect(
    host="hr3_mfinance",
    user="hr3_mfinance",
    password="",
    database="hr3_mfinance"
)

# Fetch data from the 'hiring' table
query = """
SELECT Age, sex, job_position, status, suitability_score
FROM hiring
WHERE job_position IN ('Human Resource assistant', 'Human Resource specialist', 'Human Resource coordinator')
"""
df = pd.read_sql(query, conn)  # Use Pandas to directly read the query result

# Drop any rows with missing values
df = df.dropna()

# Encode categorical variables (e.g., 'sex', 'job_position')
df = pd.get_dummies(df, columns=['sex', 'job_position'])

# Separate features (X) and target (y)
X = df.drop(columns=['suitability_score', 'status'])  # Drop target and irrelevant columns
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
