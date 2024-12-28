from faker import Faker
import pandas as pd
from sqlalchemy import create_engine

# Initialize Faker
fake = Faker()

# Database connection
engine = create_engine('mysql+mysqlconnector://root:@localhost/db_login')

# Generate synthetic data
data = []
for _ in range(100):  # Change this number to generate more or fewer records
    data.append({
        'fName': fake.first_name(),
        'lName': fake.last_name(),
        'Age': fake.random_int(min=18, max=65),
        'sex': fake.random_element(elements=('Male', 'Female')),
        'job_position': fake.job(),
        'skills_match': fake.random_int(min=0, max=1),
        'experience_match': fake.random_int(min=0, max=1),
        'education_match': fake.random_int(min=0, max=1),
        'suitability_score': fake.random_int(min=0, max=100),  # Random score between 0 and 100
    })

# Convert to DataFrame
df = pd.DataFrame(data)

# Insert into your 'hiring' table
df.to_sql('hiring', con=engine, if_exists='append', index=False)

print(f"{len(data)} records inserted into the 'hiring' table.")
