# 🚕 Distributed Ride-Sharing Platform

> A microservices-based ride-sharing platform built to demonstrate distributed systems, event-driven architecture, asynchronous communication, service isolation, and real-time driver discovery.

![Architecture](https://img.shields.io/badge/Architecture-Microservices-blue)
![Go](https://img.shields.io/badge/Gateway-Go-00ADD8)
![Laravel](https://img.shields.io/badge/Services-Laravel-red)
![Kafka](https://img.shields.io/badge/Messaging-Apache%20Kafka-black)
![Redis](https://img.shields.io/badge/Location-Redis-red)

---

## Overview

This project is a distributed ride-sharing system inspired by platforms such as Uber and Lyft.

The application is decomposed into **seven independent services**, each responsible for a specific business capability.

Instead of having a single monolithic application handling authentication, rides, drivers, payments, locations, and notifications, responsibilities are distributed across isolated services that communicate through APIs and asynchronous events.

### Core Services
 - Driver Service
 - Location Service
 - Notification Service
 - Payment Service
 - Ride Service
 - User Service

### Take taxi Flow

User Request -> Gateway -> RideRequestController -> publish ("ride-requested") event -> LocationService (consume - ride-requested) and publish ("nearby-drivers-found") -> DriverService (consume - nearby-drivers-found) and publish (driver-notification) event -> NotificationService (consume - driver-notification) and send notification to drivers