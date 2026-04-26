import sys
import os
import pickle
import pandas as pd
import warnings

warnings.filterwarnings("ignore")
BASE_DIR = os.path.dirname(os.path.abspath(__file__))

# 1. LOAD MODEL + ENCODERS
try:
    model = pickle.load(open(os.path.join(BASE_DIR, "model.pkl"), "rb"))
    le_symptom = pickle.load(open(os.path.join(BASE_DIR, "le_symptom.pkl"), "rb"))
    le_label = pickle.load(open(os.path.join(BASE_DIR, "le_label.pkl"), "rb"))
except:
    print("Unknown|0.00")
    sys.exit()

# 2. INPUT FROM PHP (8 Arguments)
try:
    symptom_input = sys.argv[1]
    temp          = float(sys.argv[2])
    hr            = float(sys.argv[3])
    bp_raw        = sys.argv[4]
    age           = float(sys.argv[5])
    smoking       = int(sys.argv[6])
    breastfeeding = int(sys.argv[7])
    reason_input  = sys.argv[8] # Ang "clue" para sa AI
except:
    print("Unknown|0.00")
    sys.exit()

# 3. HANDLE BP
try:
    bp = float(bp_raw.split("/")[0])
except:
    bp = 0.0

# 4. ENCODE SYMPTOM
try:
    symptom_encoded = le_symptom.transform([symptom_input])[0]
except:
    symptom_encoded = 0

# 5. PREDICT USING THE MODEL
input_data = pd.DataFrame([[symptom_encoded, temp, hr, bp, age, smoking, breastfeeding]], 
                          columns=['symptom', 'temp', 'hr', 'bp', 'age', 'smoking', 'breastfeeding'])

prediction = model.predict(input_data)
probs = model.predict_proba(input_data)[0]

label = le_label.inverse_transform(prediction)[0]
confidence = max(probs)

# 6. 🧠 SMART OVERRIDE (Fix for Monday Deadline)
# Kung ang model ay nalilito, tinitingnan natin ang 'Reason' at 'Assessment' keywords.
r_low = reason_input.lower()
s_low = symptom_input.lower()

if "immuniz" in r_low or "vaccine" in s_low or "penta" in s_low or "booster" in s_low:
    label = "Immunization"
    confidence = 0.99 
elif "prenatal" in r_low or "pregnant" in s_low or "check-up" in r_low:
    label = "Prenatal" if "prenatal" in r_low else "Check-up"
    confidence = 0.99

# 7. OUTPUT (Pinaka-importante ang formatting para sa PHP)
# Ginagawa nating laging less than or equal to 100 ang score.
final_score = confidence * 100 if confidence <= 1 else confidence
print(f"{label}|{final_score:.2f}")