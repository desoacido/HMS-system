from flask import Flask, request, jsonify
import joblib
import os
import numpy as np
from scipy.sparse import hstack, csr_matrix

app = Flask(__name__)

# Load models
BASE_DIR   = os.path.dirname(os.path.abspath(__file__))
model      = joblib.load(os.path.join(BASE_DIR, "ml", "medical_model.pkl"))
rec_model  = joblib.load(os.path.join(BASE_DIR, "ml", "recommendation_model.pkl"))
vectorizer = joblib.load(os.path.join(BASE_DIR, "ml", "vectorizer.pkl"))

def parse_bp(bp):
    try:
        parts = str(bp).split('/')
        return float(parts[0]), float(parts[1])
    except:
        return 120.0, 80.0

@app.route("/")
def home():
    return "HMS ML API is running ✅"

@app.route("/predict", methods=["POST"])
def predict():
    data = request.json

    symptom    = data.get("symptom", "")
    assessment = data.get("nurse_assessment", "")
    temp       = float(data.get("temp", 36.5))
    hr         = float(data.get("hr", 80))
    bp         = data.get("bp", "120/80")
    age        = float(data.get("age", 25))
    smoking    = float(data.get("smoking", 0))
    bf         = float(data.get("breastfeeding", 0))

    systolic, diastolic = parse_bp(bp)

    # Build features (same as train_model.py)
    text_combined = symptom + " " + assessment
    X_text = vectorizer.transform([text_combined])
    X_num  = np.array([[temp, hr, age, smoking, bf, systolic, diastolic]])
    X_num_sparse = csr_matrix(X_num)
    X_combined = hstack([X_text, X_num_sparse])

    category   = model.predict(X_combined)[0]
    probs      = model.predict_proba(X_combined)[0]
    confidence = round(max(probs) * 100, 2)
    recommend  = rec_model.predict(X_combined)[0]

    # Smart override
    s = symptom.lower()
    if any(k in s for k in ['vaccine','bakuna','bcg','penta','measles','polio','turok','immuniz']):
        category   = "Immunization"
        confidence = 99.0
        recommend  = "Administer appropriate vaccine; Record in immunization card; Schedule next dose"
    elif any(k in s for k in ['pills','injectable','implant','iud','family planning','condom','dmpa']):
        category   = "Family Planning"
        confidence = 99.0
        recommend  = "Counsel patient on chosen family planning method; Provide supply and follow-up schedule"
    elif any(k in s for k in ['prenatal','pregnant','nagbubuntis']):
        category   = "Check-up"
        confidence = 99.0
        recommend  = "Monitor weight and blood pressure and fetal heartbeat; Request urinalysis and CBC"

    return jsonify({
        "label":     category,
        "score":     confidence,
        "recommend": recommend
    })

if __name__ == "__main__":
    port = int(os.environ.get("PORT", 5000))
    app.run(host="0.0.0.0", port=port)