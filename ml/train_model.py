import pandas as pd
from sklearn.ensemble import RandomForestClassifier
from sklearn.feature_extraction.text import TfidfVectorizer
import joblib
import os

os.chdir(os.path.dirname(os.path.abspath(__file__)))

df = pd.read_csv('dataset.csv')
df = df[df['symptom'] != 'symptom'].dropna()

print(f"Success: dataset.csv loaded! Rows: {len(df)}")

X_text = df['symptom'].astype(str)

# --- CATEGORY MODEL ---
vectorizer = TfidfVectorizer()
X_vec = vectorizer.fit_transform(X_text)

cat_model = RandomForestClassifier(n_estimators=100, random_state=42)
cat_model.fit(X_vec, df['category'])

# --- RECOMMENDATION MODEL ---
rec_model = RandomForestClassifier(n_estimators=100, random_state=42)
rec_model.fit(X_vec, df['recommendation'])

joblib.dump(cat_model,   'medical_model.pkl')
joblib.dump(rec_model,   'recommendation_model.pkl')
joblib.dump(vectorizer,  'vectorizer.pkl')

print("-" * 30)
print("SUCCESS: All models saved!")
print(f"Categories: {df['category'].unique()}")
print("-" * 30)