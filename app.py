from flask import Flask, request, jsonify
import joblib
import os

app = Flask(__name__)

# Load models
BASE_DIR   = os.path.dirname(os.path.abspath(__file__))
model      = joblib.load(os.path.join(BASE_DIR, "ml", "medical_model.pkl"))
rec_model  = joblib.load(os.path.join(BASE_DIR, "ml", "recommendation_model.pkl"))
vectorizer = joblib.load(os.path.join(BASE_DIR, "ml", "vectorizer.pkl"))

@app.route("/")
def home():
    return "HMS ML API is running ✅"

@app.route("/predict", methods=["POST"])
def predict():
    data    = request.json
    symptom = data.get("symptom", "headache")

    X          = vectorizer.transform([symptom])
    category   = model.predict(X)[0]
    probs      = model.predict_proba(X)[0]
    confidence = round(max(probs) * 100, 2)
    recommend  = rec_model.predict(X)[0]

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
    app.run(host="0.0.0.0", port=5000)