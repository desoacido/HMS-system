from flask import Flask, request, jsonify
import joblib
import os

app = Flask(__name__)

BASE_DIR = os.path.dirname(os.path.abspath(__file__))

# ✅ FIXED: remove "ml" folder path
model = joblib.load(os.path.join(BASE_DIR, "medical_model.pkl"))
rec_model = joblib.load(os.path.join(BASE_DIR, "recommendation_model.pkl"))
vectorizer = joblib.load(os.path.join(BASE_DIR, "vectorizer.pkl"))

# ---- BP PARSER ----
def parse_bp(bp):
    try:
        parts = str(bp).split('/')
        return float(parts[0]), float(parts[1])
    except:
        return 120.0, 80.0

@app.route('/')
def home():
    return "ML API is running"

@app.route('/predict', methods=['POST'])
def predict():
    try:
        data = request.get_json()

        # ---- GET INPUTS (same as predict.py) ----
        symptom_input    = data.get('symptoms', "")
        temp_input       = float(data.get('temperature', 36.5))
        hr_input         = float(data.get('heart_rate', 80))
        bp_input         = data.get('blood_pressure', "120/80")
        age_input        = float(data.get('age', 25))
        smoking_input    = float(data.get('smoking', 0))
        bf_input         = float(data.get('breastfeeding', 0))
        assessment_input = data.get('assessment', "")

        systolic, diastolic = parse_bp(bp_input)

        # ---- BUILD FEATURES ----
        text_combined = symptom_input + " " + assessment_input
        X_text = vectorizer.transform([text_combined])

        X_num = np.array([[temp_input, hr_input, age_input, smoking_input, bf_input, systolic, diastolic]])
        X_num_sparse = csr_matrix(X_num)

        X_combined = hstack([X_text, X_num_sparse])

        # ---- PREDICTION ----
        category   = cat_model.predict(X_combined)[0]
        probs      = cat_model.predict_proba(X_combined)[0]
        confidence = float(max(probs) * 100)
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

        return jsonify({
            "category": category,
            "confidence": round(confidence, 2),
            "recommendation": recommend
        })

    except Exception as e:
        return jsonify({
            "category": "Unknown",
            "confidence": 0.00,
            "recommendation": "Prediction error"
        })

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=10000)
