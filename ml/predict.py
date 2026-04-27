import sys
import os
import joblib
import warnings

warnings.filterwarnings("ignore")
BASE_DIR = os.path.dirname(os.path.abspath(__file__))

try:
    cat_model   = joblib.load(os.path.join(BASE_DIR, "medical_model.pkl"))
    rec_model   = joblib.load(os.path.join(BASE_DIR, "recommendation_model.pkl"))
    vectorizer  = joblib.load(os.path.join(BASE_DIR, "vectorizer.pkl"))
except Exception as e:
    print(f"Unknown|0.00|Error loading models.")
    sys.exit()

try:
    symptom_input = sys.argv[1]
    reason_input  = sys.argv[8] if len(sys.argv) > 8 else ""
except:
    print("Unknown|0.00|Invalid input.")
    sys.exit()

try:
    X = vectorizer.transform([symptom_input])

    category    = cat_model.predict(X)[0]
    probs       = cat_model.predict_proba(X)[0]
    confidence  = max(probs) * 100
    recommend   = rec_model.predict(X)[0]

    # Smart override
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

    # Output: Label|Score|Recommendation
    print(f"{category}|{confidence:.2f}|{recommend}")

except Exception as e:
    print(f"Unknown|0.00|Prediction error.")
    sys.exit()