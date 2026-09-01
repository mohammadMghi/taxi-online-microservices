# 🚕 Distributed Ride-Sharing Platform

> A microservices-based ride-sharing platform built to demonstrate distributed systems, event-driven architecture, asynchronous communication, service isolation, and real-time driver discovery.

![Architecture](https://img.shields.io/badge/Architecture-Microservices-blue)
![Go](https://img.shields.io/badge/Gateway-Go-00ADD8)
![Laravel](https://img.shields.io/badge/Services-Laravel-red)
![Kafka](https://img.shields.io/badge/Messaging-Apache%20Kafka-black)
![Redis](https://img.shields.io/badge/Location-Redis-red)

---

## ✨ Overview

This project is a distributed ride-sharing system inspired by platforms such as Uber and Lyft.

The application is decomposed into **seven independent services**, each responsible for a specific business capability.

Instead of having a single monolithic application handling authentication, rides, drivers, payments, locations, and notifications, responsibilities are distributed across isolated services that communicate through APIs and asynchronous events.

### Core Services

| Service | Responsibility |
|---|---|
| 🚪 **Gateway** | Single entry point for clients and API routing |
| 👤 **User Service** | Authentication, registration, and user management |
| 🚗 **Driver Service** | Driver management and nearby-driver selection |
| 📍 **Location Service** | Driver locations and geographical operations |
| 🚕 **Ride Service** | Ride creation and ride lifecycle |
| 🔔 **Notification Service** | Driver notifications and ride-request delivery |
| 💳 **Payment Service** | Payment-related operations |

---

# 🏗️ Architecture

The system follows a **Microservices + Event-Driven Architecture**.

```text
                         ┌──────────────────┐
                         │      Client      │
                         └────────┬─────────┘
                                  │
                                  ▼
                         ┌──────────────────┐
                         │     Gateway      │
                         │       Go         │
                         │      :8080       │
                         └────────┬─────────┘
                                  │
             ┌────────────────────┼────────────────────┐
             │                    │                    │
             ▼                    ▼                    ▼
      ┌─────────────┐      ┌─────────────┐      ┌─────────────┐
      │ User Service│      │ Ride Service │      │Location Svc │
      │    :8000    │      │    :8005     │      │    :8004    │
      └─────────────┘      └──────┬──────┘      └──────┬──────┘
                                  │                    │
                                  │                    │
                                  ▼                    ▼
                           ┌──────────────────────────────┐
                           │         Apache Kafka          │
                           │       Event Backbone          │
                           └──────────────┬───────────────┘
                                          │
                         ┌────────────────┼────────────────┐
                         │                │                │
                         ▼                ▼                ▼
                  ┌─────────────┐ ┌─────────────┐ ┌──────────────┐
                  │Driver Svc   │ │Notification │ │ Payment Svc  │
                  │    :8002    │ │    :8001    │ │    :8003     │
                  └─────────────┘ └─────────────┘ └──────────────┘
```

---

# 🔄 Ride Request Flow

The main ride-request workflow is event-driven.

When a passenger requests a ride, the request travels through several stages:

```text
Passenger
   │
   ▼
Gateway
   │
   ▼
Ride Service
   │
   ├── Create Ride
   │
   ├── Store Ride in Database
   │
   └── Publish Ride Event
           │
           ▼
        Apache Kafka
           │
           ▼
    Location Service
           │
           ├── Validate Location
           │
           └── Publish Location Event
                    │
                    ▼
                 Kafka
                    │
                    ▼
             Driver Service
                    │
                    ├── Find Nearby Drivers
                    │
                    └── Publish Driver Event
                             │
                             ▼
                          Kafka
                             │
                             ▼
                  Notification Service
                             │
                             ▼
                    Notify Available Drivers
```

### Step 1 — Create Ride

The request first reaches the **Ride Service**.

The service:

1. Validates the ride request.
2. Creates the ride in its database.
3. Publishes a ride event to Kafka.

Example:

```json
{
    "pickup_location": "Tehran",
    "dropoff_location": "Tehran/example",
    "pickup_lat": 33,
    "pickup_lng": 34,
    "dropoff_lat": 21,
    "dropoff_lng": 33
}
```

---

### Step 2 — Location Processing

The **Location Service** consumes the relevant event.

It is responsible for location-related operations and validating/processing geographical information.

After processing the location, it publishes the result through Kafka.

---

### Step 3 — Nearby Driver Discovery

The **Driver Service** receives the location information and searches for available drivers near the pickup location.

```text
                    Pickup Location
                           │
                           ▼
                ┌─────────────────────┐
                │ Driver Location Data│
                └──────────┬──────────┘
                           │
                           ▼
                  Nearby Driver Search
                           │
                           ▼
                    Available Drivers
```

The selected drivers are then published as an event.

---

### Step 4 — Driver Notification

The **Notification Service** consumes the driver-selection event.

It is responsible for delivering the ride request to the appropriate drivers.

```text
Driver Service
      │
      │ Nearby Drivers Found
      ▼
    Kafka
      │
      ▼
Notification Service
      │
      ├── Driver A
      ├── Driver B
      └── Driver C
```

This keeps **driver selection** and **notification delivery** decoupled.

---

# 📨 Event-Driven Communication

Apache Kafka acts as the asynchronous communication backbone of the application.

A simplified event flow looks like:

```text
┌──────────────┐
│ Ride Service │
└──────┬───────┘
       │
       │ RideRequested
       ▼
   ┌───────┐
   │ Kafka │
   └───┬───┘
       │
       ▼
┌─────────────────┐
│ Location Service│
└────────┬────────┘
         │
         │ LocationProcessed
         ▼
     ┌───────┐
     │ Kafka │
     └───┬───┘
         │
         ▼
┌────────────────┐
│ Driver Service │
└───────┬────────┘
        │
        │ NearbyDriversFound
        ▼
     ┌───────┐
     │ Kafka │
     └───┬───┘
         │
         ▼
┌──────────────────────┐
│ Notification Service │
└──────────────────────┘
```

This architecture provides:

- **Loose coupling**
- **Asynchronous processing**
- **Independent service scaling**
- **Failure isolation**
- **Event-driven workflows**
- **Clear separation of responsibilities**

---

# 🧩 Services

## 🚪 Gateway

The Gateway is the public entry point of the system.

```text
Client
  │
  ▼
Gateway :8080
  │
  ├── /api/v1/auth/*
  ├── /api/v1/rides/*
  └── /api/v1/locations/*
```

The Gateway is implemented using **Go**.

---

## 👤 User Service

Responsible for:

- User registration
- Authentication
- User management
- JWT authentication

### Sign Up

```bash
curl -X POST http://localhost:8080/api/v1/auth/signup \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Mohammad",
    "email": "mohammad@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'
```

### Sign In

```bash
curl -X POST http://localhost:8080/api/v1/auth/signin \
  -H "Content-Type: application/json" \
  -d '{
    "email": "mohammad@example.com",
    "password": "password123"
  }'
```

The returned JWT can then be used to authenticate protected endpoints.

---

# 🚕 Ride Service

The Ride Service manages the ride lifecycle.

Example ride request:

```bash
curl -X POST http://localhost:8080/api/v1/rides/take \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer <ACCESS_TOKEN>" \
  -d '{
    "pickup_location": "Tehran",
    "dropoff_location": "Tehran/example",
    "pickup_lat": 33,
    "pickup_lng": 34,
    "dropoff_lat": 21,
    "dropoff_lng": 33
  }'
```

The Ride Service:

1. Creates the ride.
2. Stores it in the database.
3. Publishes a ride event to Kafka.
4. Allows downstream services to process the request asynchronously.

---

# 📍 Location Service

The Location Service manages driver location information.

Example:

```bash
curl -X POST http://localhost:8080/api/v1/locations/driver_current_location \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer <ACCESS_TOKEN>" \
  -d '{
    "lat": 2,
    "long": 4
  }'
```

Driver locations are used by the Driver Service to perform geographical searches and identify nearby drivers.

---

# 🚗 Driver Service

The Driver Service is responsible for:

- Driver management
- Driver availability
- Nearby driver discovery
- Selecting drivers for ride requests

A simplified workflow:

```text
Ride Request
     │
     ▼
Pickup Coordinates
     │
     ▼
Driver Service
     │
     ▼
Geospatial Search
     │
     ▼
Nearby Available Drivers
```

---

# 🔔 Notification Service

The Notification Service receives events from Kafka and handles driver notifications.

Its responsibility is intentionally separated from driver discovery.

```text
Driver Service
      │
      │ NearbyDriversFound
      ▼
    Kafka
      │
      ▼
Notification Service
      │
      ▼
Drivers
```

This allows the notification system to evolve independently from the driver-selection logic.

---

# 💳 Payment Service

The Payment Service is responsible for payment-related functionality.

The service is intentionally isolated from the Ride Service so that payment logic does not become tightly coupled to ride-management logic.

Future payment workflows can consume ride lifecycle events through Kafka.

---

# 🔐 Authentication

The API uses JWT-based authentication.

Protected requests require:

```http
Authorization: Bearer <ACCESS_TOKEN>
```

For security reasons, **never commit real JWT tokens to the repository**.

For local development:

```bash
export ACCESS_TOKEN="your-token"
```

Then:

```bash
-H "Authorization: Bearer $ACCESS_TOKEN"
```

---

# 🛠️ Technology Stack

| Technology | Purpose |
|---|---|
| **Go** | API Gateway |
| **Laravel / PHP** | Backend Microservices |
| **Apache Kafka** | Event-driven communication |
| **Redis** | Location and fast geographical operations |
| **MySQL** | Persistent service data |
| **JWT** | Authentication |
| **Docker** | Infrastructure and service isolation |
| **tmux** | Local development process management |

---

# 📂 Repository Structure

```text
.
├── Gateway/
│
├── UserService/
│
├── DriverService/
│
├── LocationService/
│
├── NotificationService/
│
├── PaymentService/
│
├── RideService/
│
├── start.sh
│
└── README.md
```

Each service contains its own application logic and can be developed and run independently.

---

# 🚀 Running the Project

The repository contains a `start.sh` script that starts all services inside a dedicated `tmux` session.

Make the script executable:

```bash
chmod +x start.sh
```

Then run:

```bash
./start.sh
```

The development environment launches:

```text
taxi
│
├── Gateway             :8080
├── UserService         :8000
├── NotificationService :8001
├── DriverService       :8002
├── PaymentService      :8003
├── LocationService     :8004
└── RideService         :8005
```

To attach to the running tmux session:

```bash
tmux attach -t taxi
```

---

# 🖥️ Development Environment

The `start.sh` script creates a tmux session named:

```text
taxi
```

Each microservice runs inside its own tmux window.

```text
┌─────────────────────────────────────────────┐
│                 tmux: taxi                  │
├─────────────────────────────────────────────┤
│ Gateway             → Go :8080              │
│ UserService         → Laravel :8000         │
│ NotificationService → Laravel :8001         │
│ DriverService       → Laravel :8002         │
│ PaymentService      → Laravel :8003         │
│ LocationService     → Laravel :8004         │
│ RideService         → Laravel :8005         │
└─────────────────────────────────────────────┘
```

---

# 🎯 Architectural Goals

This project is primarily designed to explore practical distributed-system concepts rather than simply implementing CRUD endpoints.

### Concepts Demonstrated

- Microservices Architecture
- API Gateway Pattern
- Event-Driven Architecture
- Asynchronous Communication
- Apache Kafka
- Redis Geospatial Operations
- Service Isolation
- Distributed Workflows
- JWT Authentication
- Independent Service Ownership
- Event-Based Service Coordination
- Failure Isolation
- Horizontal Scalability

---

# 🧠 Why Microservices?

The purpose of splitting the application into multiple services is not simply to create more processes.

Each service represents a distinct **business responsibility**.

```text
Ride Service
    │
    └── Owns ride lifecycle

Driver Service
    │
    └── Owns driver operations

Location Service
    │
    └── Owns geographical state

Notification Service
    │
    └── Owns notification delivery

Payment Service
    │
    └── Owns payment operations

User Service
    │
    └── Owns identity and authentication

Gateway
    │
    └── Owns external API entry point
```

This separation allows individual components to evolve, scale, and fail independently.

Kafka provides asynchronous coordination between the services.

---

# 🔮 Future Improvements

- [ ] Docker Compose for the complete local environment
- [ ] Kafka topic strategy and partitioning
- [ ] Dead Letter Queues
- [ ] Retry mechanisms
- [ ] Idempotent event consumers
- [ ] Distributed tracing
- [ ] OpenTelemetry
- [ ] Prometheus + Grafana monitoring
- [ ] Circuit breakers
- [ ] Service health checks
- [ ] OpenAPI / Swagger documentation
- [ ] Automated integration tests
- [ ] Load testing with k6
- [ ] Driver acceptance/rejection workflow
- [ ] Ride cancellation
- [ ] Ride completion
- [ ] Driver availability management
- [ ] Payment processing

---

# 📜 End-to-End Ride Flow

```text
                         ┌─────────────┐
                         │   Client    │
                         └──────┬──────┘
                                │
                                ▼
                         ┌─────────────┐
                         │   Gateway   │
                         │    :8080    │
                         └──────┬──────┘
                                │
                                ▼
                         ┌─────────────┐
                         │Ride Service │
                         │    :8005    │
                         └──────┬──────┘
                                │
                          Create Ride
                                │
                                ▼
                         ┌─────────────┐
                         │  Database   │
                         └─────────────┘
                                │
                         Ride Requested
                                │
                                ▼
                           ┌────────┐
                           │ Kafka  │
                           └───┬────┘
                               │
                               ▼
                      ┌─────────────────┐
                      │Location Service │
                      │     :8004       │
                      └────────┬────────┘
                               │
                        Location Event
                               │
                               ▼
                           ┌────────┐
                           │ Kafka  │
                           └───┬────┘
                               │
                               ▼
                       ┌────────────────┐
                       │ Driver Service │
                       │     :8002      │
                       └───────┬────────┘
                               │
                    Find Nearby Drivers
                               │
                               ▼
                           ┌────────┐
                           │ Kafka  │
                           └───┬────┘
                               │
                               ▼
                    ┌──────────────────────┐
                    │ Notification Service │
                    │        :8001         │
                    └──────────┬───────────┘
                               │
                               ▼
                       🚗 Available Drivers
```

---

# ⭐ Project Philosophy

> **The goal of this project is to explore how a real-world ride-sharing platform can be decomposed into independent services and coordinated through asynchronous, event-driven communication.**

The interesting part of the project is not only the HTTP API.

It is what happens **between the services**.

---

## 📌 Status

This project is actively under development.

The architecture and individual services may evolve as new distributed-system concepts and production-grade patterns are introduced.

tutorial : https://www.youtube.com/playlist?list=PLGwdeHAo745o
