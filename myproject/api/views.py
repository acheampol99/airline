from django.shortcuts import render

# Create your views here.
import os
import joblib
import pandas as pd

from functools import lru_cache
from rest_framework.decorators import api_view
from rest_framework.response import Response
from django.http import JsonResponse

# =========================
# BASE PATH
# =========================
BASE_DIR = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))

MODEL_PATH = os.path.join(BASE_DIR, "model", "best_model.pkl")
ENCODER_PATH = os.path.join(BASE_DIR, "model", "label_encoder.pkl")

# =========================
# LOAD ONCE
# =========================
_model = None
_encoder = None

def load_assets():
    global _model, _encoder
    if _model is None or _encoder is None:
        _model = joblib.load(MODEL_PATH)
        _encoder = joblib.load(ENCODER_PATH)
    return _model, _encoder


# =========================
# CACHE KEY (DICT BASED)
# =========================
def make_key(data: dict):
    return tuple(sorted(data.items()))


# =========================
# CACHED PREDICTION
# =========================
@lru_cache(maxsize=1024)
def cached_predict(key):
    model, encoder = load_assets()

    # convert key back to dict
    data_dict = dict(key)

    # IMPORTANT: DataFrame with column names
    df = pd.DataFrame([data_dict])

    pred = model.predict(df)[0]
    confidence = max(model.predict_proba(df)[0])

    label = encoder.inverse_transform([pred])[0]

    return label, float(confidence)


# =========================
# API ENDPOINT
# =========================
@api_view(['POST'])
def predict(request):
    data = request.data

    # validate required fields (optional but recommended)
    required_fields = [
        "Gender", "Customer_Type", "Type_of_Travel", "Class",
        "Inflight_wifi_service", "Departure_Arrival_time_convenient",
        "Ease_of_Online_booking", "Gate_location", "Food_and_drink",
        "Online_boarding", "Seat_comfort", "Inflight_entertainment",
        "On_board_service", "Leg_room_service", "Baggage_handling",
        "Checkin_service", "Inflight_service", "Cleanliness"
    ]

    for field in required_fields:
        if field not in data:
            return Response({
                "status": "error",
                "message": f"Missing field: {field}"
            }, status=400)

    key = make_key(data)

    label, confidence = cached_predict(key)

    return Response({
        "status": "success",
        "prediction": label,
        "confidence": confidence
    })


# =========================
# HEALTH CHECK
# =========================
def home(request):
    return JsonResponse({
        "message": "Airline Satisfaction API is running",
        "endpoint": "/api/predict/"
    })


# preload
load_assets()