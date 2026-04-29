import pandas as pd
from sklearn.ensemble import RandomForestClassifier
from sklearn.feature_extraction.text import TfidfVectorizer
from scipy.sparse import hstack
import joblib
import os

os.chdir(os.path.dirname(os.path.abspath(__file__)))

# Load dataset
df = pd.read_csv('dataset.csv')
df = df[df['symptom'] != 'symptom'].dropna()
print(f"Success: dataset.csv loaded! Rows: {len(df)}")

# ---- PARSE BP (e.g. "120/80" → systolic=120, diastolic=80) ----
def parse_bp(bp):
    try:
        parts = str(bp).split('/')
        return int(parts[0]), int(parts[1])
    except:
        return 120, 80

df['systolic'], df['diastolic'] = zip(*df['bp'].apply(parse_bp))

# ---- TEXT FEATURES (symptom + nurse_assessment combined) ----
df['text_combined'] = df['symptom'].astype(str) + " " + df['nurse_assessment'].astype(str)

vectorizer = TfidfVectorizer()
X_text = vectorizer.fit_transform(df['text_combined'])

# ---- NUMERICAL FEATURES ----
X_num = df[['temp', 'hr', 'age', 'smoking', 'breastfeeding', 'systolic', 'diastolic']].astype(float).values
from scipy.sparse import csr_matrix
X_num_sparse = csr_matrix(X_num)

# ---- COMBINE TEXT + NUMERICAL ----
X_combined = hstack([X_text, X_num_sparse])

# ---- CATEGORY MODEL ----
cat_model = RandomForestClassifier(n_estimators=100, random_state=42)
cat_model.fit(X_combined, df['category'])

# ---- RECOMMENDATION MODEL ----
rec_model = RandomForestClassifier(n_estimators=100, random_state=42)
rec_model.fit(X_combined, df['recommendation'])

# ---- SAVE MODELS ----
joblib.dump(cat_model,  'medical_model.pkl')
joblib.dump(rec_model,  'recommendation_model.pkl')
joblib.dump(vectorizer, 'vectorizer.pkl')

print("-" * 30)
print("SUCCESS: All models saved!")
print(f"Categories: {df['category'].unique()}")
print("-" * 30)
