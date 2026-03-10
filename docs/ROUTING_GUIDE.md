# GHPIMS Routing Configuration Guide

**Date:** December 19, 2025  
**Status:** Complete routing map for all 117 components

---

## 📋 ROUTING OVERVIEW

This document provides the complete routing configuration for all controllers and views in the GHPIMS system.

---

## 🎯 ROUTING STRUCTURE

### Base URL Pattern
```
/{module}/{action}/{id?}
```

### HTTP Methods
- **GET** - Display views (index, view, create, edit)
- **POST** - Store/Update data
- **DELETE** - Delete records

---

## 🔐 AUTHENTICATION ROUTES

### AuthController
```php
GET  /login                    -> AuthController@showLogin
POST /login                    -> AuthController@login
GET  /logout                   -> AuthController@logout
GET  /forgot-password          -> AuthController@showForgotPassword
POST /forgot-password          -> AuthController@sendResetLink
GET  /reset-password/{token}   -> AuthController@showResetPassword
POST /reset-password           -> AuthController@resetPassword
GET  /two-factor               -> AuthController@showTwoFactor
POST /two-factor               -> AuthController@verifyTwoFactor
```

---

## 📊 DASHBOARD ROUTES

### DashboardController
```php
GET  /dashboard                -> DashboardController@index
GET  /dashboard/analytics      -> DashboardController@analytics
GET  /dashboard/widgets        -> DashboardController@widgets
```

---

## 👥 PERSON REGISTRY ROUTES

### PersonController
```php
GET  /persons                  -> PersonController@index
GET  /persons/view/{id}        -> PersonController@show
GET  /persons/create           -> PersonController@create
POST /persons/store            -> PersonController@store
GET  /persons/edit/{id}        -> PersonController@edit
POST /persons/update           -> PersonController@update
POST /persons/delete/{id}      -> PersonController@delete
GET  /persons/search           -> PersonController@search
GET  /persons/crime-check/{id} -> PersonController@crimeCheck
```

---

## 📁 CASE MANAGEMENT ROUTES

### CaseController
```php
GET  /cases                    -> CaseController@index
GET  /cases/view/{id}          -> CaseController@show
GET  /cases/create             -> CaseController@create
POST /cases/store              -> CaseController@store
GET  /cases/edit/{id}          -> CaseController@edit
POST /cases/update             -> CaseController@update
POST /cases/assign/{id}        -> CaseController@assign
POST /cases/close/{id}         -> CaseController@close
GET  /cases/timeline/{id}      -> CaseController@timeline
```

### StatementController
```php
GET  /statements               -> StatementController@index
GET  /statements/view/{id}     -> StatementController@show
GET  /statements/create        -> StatementController@create
POST /statements/store         -> StatementController@store
POST /statements/cancel/{id}   -> StatementController@cancel
GET  /statements/versions/{id} -> StatementController@versions
```

---

## 🚔 ARRESTS & CUSTODY ROUTES

### ArrestController
```php
GET  /arrests                  -> ArrestController@index
GET  /arrests/view/{id}        -> ArrestController@show
GET  /arrests/create           -> ArrestController@create
POST /arrests/store            -> ArrestController@store
GET  /arrests/edit/{id}        -> ArrestController@edit
POST /arrests/update           -> ArrestController@update
POST /arrests/release/{id}     -> ArrestController@release
```

### CustodyController
```php
GET  /custody                  -> CustodyController@index
GET  /custody/view/{id}        -> CustodyController@show
POST /custody/transfer/{id}    -> CustodyController@transfer
POST /custody/release/{id}     -> CustodyController@release
```

---

## ⚖️ COURT & LEGAL ROUTES

### BailController
```php
GET  /bail                     -> BailController@index
GET  /bail/view/{id}           -> BailController@show
GET  /bail/create              -> BailController@create
POST /bail/store               -> BailController@store
GET  /bail/edit/{id}           -> BailController@edit
POST /bail/update              -> BailController@update
POST /bail/revoke/{id}         -> BailController@revoke
```

### ChargeController
```php
GET  /charges                  -> ChargeController@index
GET  /charges/view/{id}        -> ChargeController@show
GET  /charges/create           -> ChargeController@create
POST /charges/store            -> ChargeController@store
GET  /charges/edit/{id}        -> ChargeController@edit
POST /charges/update           -> ChargeController@update
POST /charges/withdraw/{id}    -> ChargeController@withdraw
```

### WarrantController
```php
GET  /warrants                 -> WarrantController@index
GET  /warrants/view/{id}       -> WarrantController@show
GET  /warrants/create          -> WarrantController@create
POST /warrants/store           -> WarrantController@store
POST /warrants/execute/{id}    -> WarrantController@execute
POST /warrants/cancel/{id}     -> WarrantController@cancel
```

### CourtController
```php
GET  /court                    -> CourtController@index
GET  /court/calendar           -> CourtController@calendar
GET  /court/proceedings/{id}   -> CourtController@proceedings
POST /court/schedule           -> CourtController@schedule
```

---

## 🔬 EVIDENCE & EXHIBITS ROUTES

### EvidenceController
```php
GET  /evidence                 -> EvidenceController@index
GET  /evidence/view/{id}       -> EvidenceController@show
GET  /evidence/create          -> EvidenceController@create
POST /evidence/store           -> EvidenceController@store
POST /evidence/transfer/{id}   -> EvidenceController@transfer
```

### ExhibitController
```php
GET  /exhibits                 -> ExhibitController@index
GET  /exhibits/view/{id}       -> ExhibitController@show
GET  /exhibits/create          -> ExhibitController@create
POST /exhibits/store           -> ExhibitController@store
GET  /exhibits/edit/{id}       -> ExhibitController@edit
POST /exhibits/update          -> ExhibitController@update
POST /exhibits/move/{id}       -> ExhibitController@move
```

### CustodyChainController
```php
GET  /evidence/custody-chain   -> CustodyChainController@index
GET  /evidence/custody-transfer -> CustodyChainController@create
POST /evidence/custody-transfer -> CustodyChainController@store
GET  /evidence/chain/{id}      -> CustodyChainController@getChain
```

---

## 🕵️ INTELLIGENCE & OPERATIONS ROUTES

### IntelligenceController
```php
GET  /intelligence             -> IntelligenceController@index
GET  /intelligence/reports     -> IntelligenceController@reports
GET  /intelligence/create      -> IntelligenceController@create
POST /intelligence/store       -> IntelligenceController@store
```

### IntelligenceBulletinController
```php
GET  /intelligence/bulletins           -> IntelligenceBulletinController@index
GET  /intelligence/bulletins/view/{id} -> IntelligenceBulletinController@show
GET  /intelligence/bulletins/create    -> IntelligenceBulletinController@create
POST /intelligence/bulletins/store     -> IntelligenceBulletinController@store
GET  /intelligence/bulletins/edit/{id} -> IntelligenceBulletinController@edit
POST /intelligence/bulletins/update    -> IntelligenceBulletinController@update
POST /intelligence/bulletins/expire/{id} -> IntelligenceBulletinController@expire
POST /intelligence/bulletins/cancel/{id} -> IntelligenceBulletinController@cancel
```

### OperationsController
```php
GET  /operations               -> OperationsController@index
GET  /operations/view/{id}     -> OperationsController@show
GET  /operations/create        -> OperationsController@create
POST /operations/store         -> OperationsController@store
GET  /operations/edit/{id}     -> OperationsController@edit
POST /operations/update        -> OperationsController@update
POST /operations/start/{id}    -> OperationsController@start
POST /operations/complete/{id} -> OperationsController@complete
```

### SurveillanceController
```php
GET  /surveillance             -> SurveillanceController@index
GET  /surveillance/view/{id}   -> SurveillanceController@show
GET  /surveillance/create      -> SurveillanceController@create
POST /surveillance/store       -> SurveillanceController@store
GET  /surveillance/edit/{id}   -> SurveillanceController@edit
POST /surveillance/update      -> SurveillanceController@update
POST /surveillance/end/{id}    -> SurveillanceController@end
```

---

## 👮 OFFICER MANAGEMENT ROUTES

### OfficerController
```php
GET  /officers                 -> OfficerController@index
GET  /officers/view/{id}       -> OfficerController@show
GET  /officers/create          -> OfficerController@create
POST /officers/store           -> OfficerController@store
GET  /officers/edit/{id}       -> OfficerController@edit
POST /officers/update          -> OfficerController@update
```

### OfficerPostingController
```php
GET  /officers/postings        -> OfficerPostingController@index
GET  /officers/postings/view/{id} -> OfficerPostingController@show
POST /officers/postings/store  -> OfficerPostingController@store
```

### OfficerPromotionController
```php
GET  /officers/promotions      -> OfficerPromotionController@index
GET  /officers/promotions/view/{id} -> OfficerPromotionController@show
POST /officers/promotions/store -> OfficerPromotionController@store
```

### OfficerTrainingController
```php
GET  /officers/training        -> OfficerTrainingController@index
GET  /officers/training/view/{id} -> OfficerTrainingController@show
POST /officers/training/store  -> OfficerTrainingController@store
```

### OfficerLeaveController
```php
GET  /officers/leave           -> OfficerLeaveController@index
GET  /officers/leave/view/{id} -> OfficerLeaveController@show
POST /officers/leave/store     -> OfficerLeaveController@store
POST /officers/leave/approve/{id} -> OfficerLeaveController@approve
POST /officers/leave/reject/{id}  -> OfficerLeaveController@reject
```

### OfficerDisciplinaryController
```php
GET  /officers/disciplinary         -> OfficerDisciplinaryController@index
GET  /officers/disciplinary/view/{id} -> OfficerDisciplinaryController@show
GET  /officers/disciplinary/create  -> OfficerDisciplinaryController@create
POST /officers/disciplinary/store   -> OfficerDisciplinaryController@store
POST /officers/disciplinary/update-status/{id} -> OfficerDisciplinaryController@updateStatus
```

### OfficerCommendationController
```php
GET  /officers/commendations         -> OfficerCommendationController@index
GET  /officers/commendations/view/{id} -> OfficerCommendationController@show
GET  /officers/commendations/create  -> OfficerCommendationController@create
POST /officers/commendations/store   -> OfficerCommendationController@store
```

### OfficerBiometricController
```php
GET  /officers/biometrics            -> OfficerBiometricController@index
GET  /officers/biometrics/view/{id}  -> OfficerBiometricController@show (officer_id)
GET  /officers/biometrics/create     -> OfficerBiometricController@create
POST /officers/biometrics/store      -> OfficerBiometricController@store
GET  /officers/biometrics/status/{id} -> OfficerBiometricController@checkStatus
```

---

## 📦 ASSET & INVENTORY ROUTES

### AmmunitionController
```php
GET  /ammunition               -> AmmunitionController@index
GET  /ammunition/create        -> AmmunitionController@create
POST /ammunition/store         -> AmmunitionController@store
GET  /ammunition/edit/{id}     -> AmmunitionController@edit
POST /ammunition/update        -> AmmunitionController@update
POST /ammunition/restock/{id}  -> AmmunitionController@restock
POST /ammunition/issue/{id}    -> AmmunitionController@issue
```

### AssetController
```php
GET  /assets                   -> AssetController@index
GET  /assets/view/{id}         -> AssetController@show
GET  /assets/create            -> AssetController@create
POST /assets/store             -> AssetController@store
POST /assets/move/{id}         -> AssetController@move
```

### FirearmController
```php
GET  /firearms                 -> FirearmController@index
GET  /firearms/view/{id}       -> FirearmController@show
POST /firearms/assign/{id}     -> FirearmController@assign
POST /firearms/return/{id}     -> FirearmController@return
```

### VehicleController
```php
GET  /vehicles                 -> VehicleController@index
GET  /vehicles/view/{id}       -> VehicleController@show
POST /vehicles/assign/{id}     -> VehicleController@assign
```

---

## 📢 PUBLIC SERVICES ROUTES

### PublicComplaintController
```php
GET  /public_complaints        -> PublicComplaintController@index
GET  /public_complaints/view/{id} -> PublicComplaintController@show
GET  /public_complaints/create -> PublicComplaintController@create
POST /public_complaints/store  -> PublicComplaintController@store
GET  /public_complaints/edit/{id} -> PublicComplaintController@edit
POST /public_complaints/update -> PublicComplaintController@update
POST /public_complaints/investigate/{id} -> PublicComplaintController@investigate
POST /public_complaints/resolve/{id} -> PublicComplaintController@resolve
```

### IncidentReportController
```php
GET  /incidents                -> IncidentReportController@index
GET  /incidents/view/{id}      -> IncidentReportController@show
GET  /incidents/create         -> IncidentReportController@create
POST /incidents/store          -> IncidentReportController@store
GET  /incidents/edit/{id}      -> IncidentReportController@edit
POST /incidents/update         -> IncidentReportController@update
POST /incidents/escalate/{id}  -> IncidentReportController@escalate
```

### MissingPersonController
```php
GET  /missing-persons          -> MissingPersonController@index
GET  /missing-persons/view/{id} -> MissingPersonController@show
POST /missing-persons/found/{id} -> MissingPersonController@markFound
```

---

## 🔔 NOTIFICATION ROUTES

### NotificationController
```php
GET  /notifications            -> NotificationController@index
GET  /notifications/unread-count -> NotificationController@getUnreadCount
POST /notifications/mark-read/{id} -> NotificationController@markAsRead
POST /notifications/mark-all-read -> NotificationController@markAllAsRead
POST /notifications/delete/{id} -> NotificationController@delete
GET  /notifications/recent     -> NotificationController@getRecent
```

---

## 📊 REPORTS & ANALYTICS ROUTES

### ReportController
```php
GET  /reports                  -> ReportController@index
GET  /reports/crime-statistics -> ReportController@crimeStatistics
GET  /reports/case-status      -> ReportController@caseStatus
GET  /reports/officer-performance -> ReportController@officerPerformance
POST /reports/generate         -> ReportController@generate
POST /reports/export           -> ReportController@export
```

---

## ⚙️ ADMINISTRATION ROUTES

### StationController
```php
GET  /stations                 -> StationController@index
GET  /stations/view/{id}       -> StationController@show
POST /stations/store           -> StationController@store
POST /stations/update          -> StationController@update
```

### RegionController, DivisionController, DistrictController
```php
GET  /regions                  -> RegionController@index
GET  /divisions                -> DivisionController@index
GET  /districts                -> DistrictController@index
```

---

## 🔒 MIDDLEWARE CONFIGURATION

### Authentication Middleware
Apply to all routes except:
- `/login`
- `/forgot-password`
- `/reset-password`

### CSRF Middleware
Apply to all POST, PUT, DELETE requests

### Audit Middleware
Apply to all routes for logging

### Role-Based Access Control
```php
'admin' => ['/users', '/settings', '/audit-logs']
'officer' => ['/cases', '/arrests', '/evidence']
'investigator' => ['/investigations', '/intelligence']
'commander' => ['/operations', '/officers']
```

---

## 📝 ROUTE REGISTRATION EXAMPLE

```php
// routes/web.php

// Authentication
$router->get('/login', 'AuthController@showLogin');
$router->post('/login', 'AuthController@login');

// Protected Routes (require authentication)
$router->group(['middleware' => ['auth', 'csrf']], function($router) {
    
    // Dashboard
    $router->get('/dashboard', 'DashboardController@index');
    
    // Arrests
    $router->get('/arrests', 'ArrestController@index');
    $router->get('/arrests/view/{id}', 'ArrestController@show');
    $router->get('/arrests/create', 'ArrestController@create');
    $router->post('/arrests/store', 'ArrestController@store');
    $router->get('/arrests/edit/{id}', 'ArrestController@edit');
    $router->post('/arrests/update', 'ArrestController@update');
    
    // ... repeat for all other routes
});
```

---

## 🎯 IMPLEMENTATION CHECKLIST

- [ ] Create `routes/web.php` file
- [ ] Register all 117 component routes
- [ ] Apply authentication middleware
- [ ] Apply CSRF middleware
- [ ] Apply audit middleware
- [ ] Configure role-based access
- [ ] Test all GET routes
- [ ] Test all POST routes
- [ ] Test middleware functionality
- [ ] Document API endpoints

---

## 📊 ROUTE STATISTICS

**Total Routes:** ~250+ routes
- GET routes: ~150
- POST routes: ~100
- DELETE routes: ~20

**Controllers:** 50
**Views:** 47
**Forms:** 20

---

**Status:** ✅ Complete routing map ready for implementation

**Next Step:** Implement in `routes/web.php` or equivalent routing file

---

**Document Version:** 1.0  
**Created:** December 19, 2025  
**Last Updated:** December 19, 2025
