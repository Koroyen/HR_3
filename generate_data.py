from faker import Faker
import pandas as pd
from sqlalchemy import create_engine
import random

# Initialize Faker
fake = Faker()

# Database connection
engine = create_engine('mysql+mysqlconnector://hr3_mfinance:bgn^C8sHe8k*aPC6@localhost/hr3_mfinance')

# Generate synthetic data
data = []
job_positions = ['Human Resource Assistant', 'Human Resource Specialist', 'Human Resource Coordinator']

for _ in range(100):  # Change this number to generate more or fewer records
    education = random.choice(['University of the Philippines', 'Ateneo de Manila University', 'De La Salle University', 'University of Santo Tomas'])
    otherEducation = fake.company() if education == 'Other' else ''
    
    # Adding logic for the 'job_position' column
    job_position = random.choice(job_positions)

    data.append({
        'fName': fake.first_name(),
        'lName': fake.last_name(),
        'Age': fake.random_int(min=18, max=65),
        'sex': fake.random_element(elements=('Male', 'Female')),
        'job_position': job_position,  # Using the predefined job positions
        'email': fake.email(),
        'street': fake.street_address(),
        'barangay': fake.word(),
        'city': fake.city(),  # Generate a city value
        'valid_ids': 'ID' + str(fake.random_number(digits=7)),  # Generating valid_ids
        'birthcerti': 'cert' + str(fake.random_number(digits=7)),  # Generating birthcerti
        'status': fake.random_element(elements=('Pending', 'Rejected', 'Accepted')),
        'message': fake.sentence(),
        'application_type': 'hiring',
        'is_visible': fake.random_int(0, 1),
        'suitability_score': round(random.uniform(0, 10), 1),
        'experience_years': fake.random_int(min=0, max=10),
        'experience_months': fake.random_int(min=6, max=11),
        'education': education,
        'otherEducation': otherEducation,
        'date_uploaded': fake.date_this_year(),
        'date_status_updated': fake.date_this_year(),
    })

# Convert to DataFrame
df = pd.DataFrame(data)

# Insert into your 'hiring' table
df.to_sql('hiring', con=engine, if_exists='append', index=False)

print(f"{len(data)} records inserted into the 'hiring' table.")
