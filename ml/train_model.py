import pandas as pd
from sklearn.preprocessing import LabelEncoder
from sklearn.ensemble import RandomForestClassifier
import pickle

# 1. LOAD DATA
data = pd.read_csv("dataset.csv")

# Linisin ang column names para sigurado (tanggalin ang extra spaces)
data.columns = data.columns.str.strip()

# 2. ENCODE SYMPTOM
le_symptom = LabelEncoder()
data['symptom'] = le_symptom.fit_transform(data['symptom'])

# 3. ENCODE CATEGORY (Dito natin binago ang pangalan)
le_category = LabelEncoder()
data['category_encoded'] = le_category.fit_transform(data['category'])

# 4. FEATURES (7 Columns)
X = data[['symptom', 'temp', 'hr', 'bp', 'age', 'smoking', 'breastfeeding']]
y = data['category_encoded']

# 5. TRAIN
model = RandomForestClassifier(n_estimators=100, random_state=42)
model.fit(X, y)

# 6. SAVE
pickle.dump(model, open("model.pkl", "wb"))
pickle.dump(le_symptom, open("le_symptom.pkl", "wb"))
pickle.dump(le_category, open("le_label.pkl", "wb")) # Ni-save ko pa rin as le_label.pkl para di na tayo magbago ng predict.py

print("✅ SUCCESS: Model trained with Check-up, Immunization, and Family Planning!")