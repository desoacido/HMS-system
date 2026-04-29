import sys
import os
import joblib
import warnings
import numpy as np
from scipy.sparse import hstack, csr_matrix

warnings.filterwarnings("ignore")
BASE_DIR = os.path.dirname(os.path.abspath(__file__))

# ---- LOAD MODELS ----
try:
    cat_model  = joblib.load(os.path.join(BASE_DIR, "medical_model.pkl"))
    rec_model  = joblib.load(os.path.join(BASE_DIR, "recommendation_model.pkl"))
    vectorizer = joblib.load(os.path.join(BASE_DIR, "vectorizer.pkl"))
except Exception as e:
    print(f"Unknown|0.00|Error loading models.")
    sys.exit()

# ---- GET INPUTS FROM PHP ----
# Usage: python predict.py symptom temp hr bp age smoking breastfeeding nurse_assessment
try:
    symptom_input    = sys.argv[1] if len(sys.argv) > 1 else ""
    temp_input       = float(sys.argv[2]) if len(sys.argv) > 2 else 36.5
    hr_input         = float(sys.argv[3]) if len(sys.argv) > 3 else 80
    bp_input         = sys.argv[4]        if len(sys.argv) > 4 else "120/80"
    age_input        = float(sys.argv[5]) if len(sys.argv) > 5 else 25
    smoking_input    = float(sys.argv[6]) if len(sys.argv) > 6 else 0
    bf_input         = float(sys.argv[7]) if len(sys.argv) > 7 else 0
    assessment_input = sys.argv[8]        if len(sys.argv) > 8 else ""
except:
    print("Unknown|0.00|Invalid input.")
    sys.exit()

# ---- PARSE BP ----
def parse_bp(bp):
    try:
        parts = str(bp).split('/')
        return float(parts[0]), float(parts[1])
    except:
        return 120.0, 80.0

systolic, diastolic = parse_bp(bp_input)

# ---- BUILD FEATURES (same as train_model.py) ----
try:
    text_combined = symptom_input + " " + assessment_input
    X_text = vectorizer.transform([text_combined])

    X_num = np.array([[temp_input, hr_input, age_input, smoking_input, bf_input, systolic, diastolic]])
    X_num_sparse = csr_matrix(X_num)

    X_combined = hstack([X_text, X_num_sparse])

    category   = cat_model.predict(X_combined)[0]
    probs      = cat_model.predict_proba(X_combined)[0]
    confidence = max(probs) * 100
    recommend  = rec_model.predict(X_combined)[0]

    # ---- SMART OVERRIDE ----
    s_low = symptom_input.lower()
    if any(k in s_low for k in ['vaccine','immuniz','bcg','penta','measles','polio','bakuna','turok']):
        category   = "Immunization"
        confidence = 99.0
        recommend  = "Administer appropriate vaccine. Record in immunization card. Schedule next dose."
    elif any(k in s_low for k in ['pills','injectable','implant','iud','family planning','condom']):
        category   = "Family Planning"
        confidence = 99.0
        recommend  = "Counsel patient on chosen family planning method. Provide supply and follow-up schedule."
    elif any(k in s_low for k in ['prenatal','pregnant']):
        category   = "Check-up"
        confidence = 99.0
        recommend  = "Schedule prenatal visit. Monitor weight, BP, and fetal heartbeat."

    # ---- OUTPUT: Category|Confidence|Recommendation ----
    print(f"{category}|{confidence:.2f}|{recommend}")

except Exception as e:
    print(f"Unknown|0.00|Prediction error.")
    sys.exit()