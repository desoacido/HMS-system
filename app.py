from flask import Flask, request, jsonify
import joblib
import os

app = Flask(__name__)

# safer path for deployment
MODEL_PATH = os.path.join("ml", "model.pkl")
model = joblib.load(MODEL_PATH)

@app.route("/")
def home():
    return "HMS ML API is running"

@app.route("/predict", methods=["POST"])
def predict():
    data = request.json

    features = [
        data["symptoms"],
        data["temp"],
        data["oxygen"],
        data["bp"]
    ]

    result = model.predict([features])

    return jsonify({"prediction": str(result[0])})

# IMPORTANT FOR RENDER
if __name__ == "__main__":
    app.run(host="0.0.0.0", port=5000)