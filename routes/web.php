<?php

use App\Middleware\AuthMiddleware;
use App\Middleware\GuestMiddleware;

// Authentication Routes
$router->get('/', 'AuthController@loginForm', [GuestMiddleware::class]);
$router->get('/login', 'AuthController@loginForm', [GuestMiddleware::class]);
$router->post('/login', 'AuthController@login', [GuestMiddleware::class]);
$router->get('/logout', 'AuthController@logout');
$router->get('/forgot-password', 'AuthController@forgotPasswordForm', [GuestMiddleware::class]);
$router->post('/forgot-password', 'AuthController@forgotPassword', [GuestMiddleware::class]);
$router->get('/reset-password', 'AuthController@resetPasswordForm', [GuestMiddleware::class]);
$router->post('/reset-password', 'AuthController@resetPassword', [GuestMiddleware::class]);

// Dashboard
$router->get('/dashboard', 'DashboardController@index', [AuthMiddleware::class]);

// Person Management
$router->get('/persons', 'PersonController@index', [AuthMiddleware::class]);
$router->get('/persons/search', 'PersonController@search', [AuthMiddleware::class]);
$router->get('/persons/crime-check', 'PersonController@crimeCheck', [AuthMiddleware::class]);
$router->post('/persons/crime-check', 'PersonController@crimeCheck', [AuthMiddleware::class]);
$router->post('/persons/ajax-create', 'PersonController@ajaxCreate', [AuthMiddleware::class]);
$router->get('/persons/create', 'PersonController@create', [AuthMiddleware::class]);
$router->post('/persons', 'PersonController@store', [AuthMiddleware::class]);
$router->get('/persons/{id}', 'PersonController@show', [AuthMiddleware::class]);
$router->get('/persons/{id}/edit', 'PersonController@edit', [AuthMiddleware::class]);
$router->post('/persons/{id}', 'PersonController@update', [AuthMiddleware::class]);
$router->get('/persons/{id}/crime-check', 'PersonController@crimeCheck', [AuthMiddleware::class]);
$router->post('/persons/{id}/relationships', 'PersonController@addRelationship', [AuthMiddleware::class]);
$router->post('/persons/relationships/{relationshipId}/delete', 'PersonController@deleteRelationship', [AuthMiddleware::class]);
$router->post('/persons/{id}/alerts', 'PersonController@issueAlert', [AuthMiddleware::class]);
$router->get('/persons/alerts/{alertId}', 'PersonController@getAlert', [AuthMiddleware::class]);
$router->post('/persons/alerts/{alertId}/update', 'PersonController@updateAlert', [AuthMiddleware::class]);
$router->post('/persons/alerts/{alertId}/deactivate', 'PersonController@deactivateAlert', [AuthMiddleware::class]);

// Case Management
$router->get('/cases', 'CaseController@index', [AuthMiddleware::class]);
$router->get('/cases/specialized-units', 'CaseController@specializedUnits', [AuthMiddleware::class]);
$router->get('/cases/dovvsu-workflow', 'CaseController@dovvsuWorkflow', [AuthMiddleware::class]);
$router->get('/cases/create', 'CaseController@create', [AuthMiddleware::class]);
$router->post('/cases', 'CaseController@store', [AuthMiddleware::class]);
$router->get('/cases/{id}', 'CaseController@show', [AuthMiddleware::class]);
$router->get('/cases/view/{id}', 'CaseController@show', [AuthMiddleware::class]); // Alias for backward compatibility
$router->get('/cases/{id}/edit', 'CaseController@edit', [AuthMiddleware::class]);
$router->post('/cases/{id}', 'CaseController@update', [AuthMiddleware::class]);
$router->post('/cases/{id}/suspects', 'CaseController@addSuspect', [AuthMiddleware::class]);
$router->post('/cases/{id}/suspects/{suspectId}/remove', 'CaseController@removeSuspect', [AuthMiddleware::class]);
$router->post('/cases/{id}/suspects/{suspectId}/status', 'CaseController@updateSuspectStatus', [AuthMiddleware::class]);
$router->get('/cases/{id}/suspects/{suspectId}/edit', 'CaseController@editSuspect', [AuthMiddleware::class]);
$router->post('/cases/{id}/suspects/{suspectId}/edit', 'CaseController@updateSuspect', [AuthMiddleware::class]);
$router->get('/cases/{id}/suspects/{suspectId}/upgrade', 'CaseController@upgradeSuspect', [AuthMiddleware::class]);
$router->post('/cases/{id}/suspects/{suspectId}/upgrade', 'CaseController@processUpgrade', [AuthMiddleware::class]);
$router->post('/cases/{id}/witnesses', 'CaseController@addWitness', [AuthMiddleware::class]);
$router->post('/cases/{id}/witnesses/{witnessId}/remove', 'CaseController@removeWitness', [AuthMiddleware::class]);
$router->post('/cases/{id}/evidence', 'CaseController@uploadEvidence', [AuthMiddleware::class]);
$router->get('/cases/{id}/evidence/{evidenceId}/download', 'CaseController@downloadEvidence', [AuthMiddleware::class]);
$router->post('/cases/{id}/evidence/{evidenceId}/delete', 'CaseController@deleteEvidence', [AuthMiddleware::class]);
$router->post('/cases/{id}/statements', 'CaseController@addStatement', [AuthMiddleware::class]);
$router->get('/cases/{id}/statements/{statementId}', 'CaseController@getStatement', [AuthMiddleware::class]);
$router->post('/cases/{id}/statements/{statementId}/cancel', 'CaseController@cancelStatement', [AuthMiddleware::class]);
$router->post('/cases/{id}/assignments', 'CaseController@assignOfficer', [AuthMiddleware::class]);
$router->post('/cases/{id}/assignments/{assignmentId}/reassign', 'CaseController@reassignOfficer', [AuthMiddleware::class]);
$router->post('/cases/{id}/assignments/{assignmentId}/complete', 'CaseController@completeAssignment', [AuthMiddleware::class]);
$router->post('/cases/{id}/crimes', 'CaseController@addCrime', [AuthMiddleware::class]);
$router->post('/cases/{id}/crimes/{crimeId}/delete', 'CaseController@deleteCrime', [AuthMiddleware::class]);
$router->post('/cases/{id}/referrals', 'CaseController@referCase', [AuthMiddleware::class]);

// Biometric Management - Person-based
$router->get('/persons/{id}/biometrics', 'BiometricController@capturePersonBiometrics', [AuthMiddleware::class]);
$router->post('/persons/{id}/biometrics', 'BiometricController@storePersonBiometric', [AuthMiddleware::class]);
$router->post('/persons/{id}/biometrics/bulk', 'BiometricController@storeBulkBiometrics', [AuthMiddleware::class]);
$router->get('/biometrics/{id}', 'BiometricController@viewBiometric', [AuthMiddleware::class]);
$router->post('/biometrics/{id}/delete', 'BiometricController@deleteBiometric', [AuthMiddleware::class]);

// Legacy routes for backward compatibility (redirect to person-based)
$router->get('/suspects/{id}/biometrics', 'BiometricController@captureSuspectBiometrics', [AuthMiddleware::class]);
$router->post('/suspects/{id}/biometrics', 'BiometricController@storeSuspectBiometric', [AuthMiddleware::class]);

// Investigation Management
$router->get('/investigations', 'InvestigationController@index', [AuthMiddleware::class]);
$router->get('/investigations/{id}', 'InvestigationController@show', [AuthMiddleware::class]);
$router->post('/investigations/{id}/tasks', 'InvestigationController@addTask', [AuthMiddleware::class]);
$router->post('/investigations/{id}/checklist', 'InvestigationController@updateChecklistItem', [AuthMiddleware::class]);
$router->post('/investigations/{id}/milestones', 'InvestigationController@addMilestone', [AuthMiddleware::class]);
$router->post('/investigations/tasks/{id}/status', 'InvestigationController@updateTaskStatus', [AuthMiddleware::class]);

// Officer Management
$router->get('/officers', 'OfficerController@index', [AuthMiddleware::class]);
$router->get('/officers/create', 'OfficerController@create', [AuthMiddleware::class]);
$router->get('/officers/search/{query}', 'OfficerController@search', [AuthMiddleware::class]);

// Officer-specific module routes (must come before /officers/{id})
$router->get('/officers/biometrics', 'OfficerBiometricController@index', [AuthMiddleware::class]);
$router->get('/officers/biometrics/view/{id}', 'OfficerBiometricController@show', [AuthMiddleware::class]);
$router->get('/officers/biometrics/create', 'OfficerBiometricController@create', [AuthMiddleware::class]);
$router->post('/officers/biometrics/store', 'OfficerBiometricController@store', [AuthMiddleware::class]);
$router->get('/officers/biometrics/status/{id}', 'OfficerBiometricController@checkStatus', [AuthMiddleware::class]);

$router->get('/officers/disciplinary', 'OfficerDisciplinaryController@index', [AuthMiddleware::class]);
$router->get('/officers/disciplinary/view/{id}', 'OfficerDisciplinaryController@show', [AuthMiddleware::class]);
$router->get('/officers/disciplinary/create', 'OfficerDisciplinaryController@create', [AuthMiddleware::class]);
$router->post('/officers/disciplinary/store', 'OfficerDisciplinaryController@store', [AuthMiddleware::class]);
$router->post('/officers/disciplinary/update-status/{id}', 'OfficerDisciplinaryController@updateStatus', [AuthMiddleware::class]);

$router->get('/officers/commendations', 'OfficerCommendationController@index', [AuthMiddleware::class]);
$router->get('/officers/commendations/view/{id}', 'OfficerCommendationController@show', [AuthMiddleware::class]);
$router->get('/officers/commendations/create', 'OfficerCommendationController@create', [AuthMiddleware::class]);
$router->post('/officers/commendations/store', 'OfficerCommendationController@store', [AuthMiddleware::class]);

$router->get('/officers/leave', 'OfficerLeaveController@index', [AuthMiddleware::class]);
$router->get('/officers/leave/view/{id}', 'OfficerLeaveController@show', [AuthMiddleware::class]);
$router->post('/officers/leave/store', 'OfficerLeaveController@store', [AuthMiddleware::class]);
$router->post('/officers/leave/approve/{id}', 'OfficerLeaveController@approve', [AuthMiddleware::class]);
$router->post('/officers/leave/reject/{id}', 'OfficerLeaveController@reject', [AuthMiddleware::class]);

$router->get('/officers/training', 'OfficerTrainingController@index', [AuthMiddleware::class]);
$router->get('/officers/training/view/{id}', 'OfficerTrainingController@show', [AuthMiddleware::class]);
$router->post('/officers/training/store', 'OfficerTrainingController@store', [AuthMiddleware::class]);

$router->get('/officers/promotions', 'OfficerPromotionController@index', [AuthMiddleware::class]);
$router->get('/officers/promotions/view/{id}', 'OfficerPromotionController@show', [AuthMiddleware::class]);
$router->post('/officers/promotions/store', 'OfficerPromotionController@store', [AuthMiddleware::class]);

$router->get('/officers/postings', 'OfficerPostingController@index', [AuthMiddleware::class]);
$router->get('/officers/postings/view/{id}', 'OfficerPostingController@show', [AuthMiddleware::class]);
$router->post('/officers/postings/store', 'OfficerPostingController@store', [AuthMiddleware::class]);

// Generic officer routes (must come after specific paths)
$router->post('/officers', 'OfficerController@store', [AuthMiddleware::class]);
$router->get('/officers/{id}', 'OfficerController@show', [AuthMiddleware::class]);
$router->get('/officers/{id}/edit', 'OfficerController@edit', [AuthMiddleware::class]);
$router->post('/officers/{id}', 'OfficerController@update', [AuthMiddleware::class]);
$router->post('/officers/{id}/transfer', 'OfficerController@transfer', [AuthMiddleware::class]);
$router->post('/officers/{id}/promote', 'OfficerController@promote', [AuthMiddleware::class]);

// Evidence Management
$router->get('/evidence', 'EvidenceController@list', [AuthMiddleware::class]);
$router->get('/evidence/custody-chain', 'CustodyChainController@index', [AuthMiddleware::class]);
$router->get('/evidence/custody-transfer', 'CustodyChainController@create', [AuthMiddleware::class]);
$router->post('/evidence/custody-transfer', 'CustodyChainController@store', [AuthMiddleware::class]);
$router->get('/evidence/chain/{id}', 'CustodyChainController@getChain', [AuthMiddleware::class]);
$router->get('/cases/{id}/evidence', 'EvidenceController@index', [AuthMiddleware::class]);
$router->post('/cases/{id}/evidence', 'EvidenceController@store', [AuthMiddleware::class]);
$router->get('/evidence/{id}', 'EvidenceController@show', [AuthMiddleware::class]);
$router->post('/evidence/{id}/transfer', 'EvidenceController@transferCustody', [AuthMiddleware::class]);
$router->post('/evidence/{id}/status', 'EvidenceController@updateStatus', [AuthMiddleware::class]);

// Court Management
$router->get('/cases/{id}/court', 'CourtController@index', [AuthMiddleware::class]);
$router->post('/cases/{id}/court/proceedings', 'CourtController@addProceeding', [AuthMiddleware::class]);
$router->post('/cases/{id}/court/charges', 'CourtController@addCharges', [AuthMiddleware::class]);
$router->post('/cases/{id}/court/warrants', 'CourtController@issueWarrant', [AuthMiddleware::class]);
$router->post('/cases/{id}/court/bail', 'CourtController@recordBail', [AuthMiddleware::class]);

// Station Management
$router->get('/stations', 'StationController@index', [AuthMiddleware::class]);
$router->get('/stations/create', 'StationController@create', [AuthMiddleware::class]);
$router->post('/stations', 'StationController@store', [AuthMiddleware::class]);
$router->get('/stations/{id}', 'StationController@show', [AuthMiddleware::class]);
$router->get('/stations/{id}/edit', 'StationController@edit', [AuthMiddleware::class]);
$router->post('/stations/{id}', 'StationController@update', [AuthMiddleware::class]);

// Region Management
$router->get('/regions', 'RegionController@index', [AuthMiddleware::class]);
$router->get('/regions/create', 'RegionController@create', [AuthMiddleware::class]);
$router->post('/regions', 'RegionController@store', [AuthMiddleware::class]);
$router->get('/regions/{id}', 'RegionController@show', [AuthMiddleware::class]);
$router->get('/regions/{id}/edit', 'RegionController@edit', [AuthMiddleware::class]);
$router->post('/regions/{id}', 'RegionController@update', [AuthMiddleware::class]);

// Division Management
$router->get('/divisions', 'DivisionController@index', [AuthMiddleware::class]);
$router->get('/divisions/create', 'DivisionController@create', [AuthMiddleware::class]);
$router->post('/divisions', 'DivisionController@store', [AuthMiddleware::class]);
$router->get('/divisions/{id}', 'DivisionController@show', [AuthMiddleware::class]);
$router->get('/divisions/{id}/edit', 'DivisionController@edit', [AuthMiddleware::class]);
$router->post('/divisions/{id}', 'DivisionController@update', [AuthMiddleware::class]);

// District Management
$router->get('/districts', 'DistrictController@index', [AuthMiddleware::class]);
$router->get('/districts/create', 'DistrictController@create', [AuthMiddleware::class]);
$router->post('/districts', 'DistrictController@store', [AuthMiddleware::class]);
$router->get('/districts/{id}', 'DistrictController@show', [AuthMiddleware::class]);
$router->get('/districts/{id}/edit', 'DistrictController@edit', [AuthMiddleware::class]);
$router->post('/districts/{id}', 'DistrictController@update', [AuthMiddleware::class]);

// Unit Management
$router->get('/units', 'UnitController@index', [AuthMiddleware::class]);
$router->get('/units/create', 'UnitController@create', [AuthMiddleware::class]);
$router->post('/units', 'UnitController@store', [AuthMiddleware::class]);
$router->get('/units/{id}', 'UnitController@show', [AuthMiddleware::class]);
$router->get('/units/{id}/edit', 'UnitController@edit', [AuthMiddleware::class]);
$router->post('/units/{id}', 'UnitController@update', [AuthMiddleware::class]);

// Document Management
$router->post('/cases/{id}/documents', 'DocumentController@uploadCaseDocument', [AuthMiddleware::class]);
$router->get('/cases/{id}/documents', 'DocumentController@getCaseDocuments', [AuthMiddleware::class]);
$router->post('/documents/{id}/delete', 'DocumentController@deleteDocument', [AuthMiddleware::class]);

// Case Notes
$router->post('/cases/{id}/notes', 'CaseNoteController@addNote', [AuthMiddleware::class]);
$router->post('/notes/{id}/update', 'CaseNoteController@updateNote', [AuthMiddleware::class]);
$router->post('/notes/{id}/delete', 'CaseNoteController@deleteNote', [AuthMiddleware::class]);

// Duty Roster Management
$router->get('/duty-roster', 'DutyRosterController@index', [AuthMiddleware::class]);
$router->get('/duty-roster/create', 'DutyRosterController@create', [AuthMiddleware::class]);
$router->post('/duty-roster/store', 'DutyRosterController@store', [AuthMiddleware::class]);
$router->get('/duty-roster/{id}/edit', 'DutyRosterController@edit', [AuthMiddleware::class]);
$router->post('/duty-roster/{id}', 'DutyRosterController@update', [AuthMiddleware::class]);
$router->post('/duty-roster/{id}/delete', 'DutyRosterController@delete', [AuthMiddleware::class]);
$router->get('/duty-roster/weekly', 'DutyRosterController@weekly', [AuthMiddleware::class]);

// Patrol Log Management
$router->get('/patrol-logs', 'PatrolLogController@index', [AuthMiddleware::class]);
$router->get('/patrol-logs/create', 'PatrolLogController@create', [AuthMiddleware::class]);
$router->post('/patrol-logs/store', 'PatrolLogController@store', [AuthMiddleware::class]);
$router->get('/patrol-logs/{id}', 'PatrolLogController@show', [AuthMiddleware::class]);
$router->get('/patrol-logs/{id}/edit', 'PatrolLogController@edit', [AuthMiddleware::class]);
$router->post('/patrol-logs/{id}', 'PatrolLogController@update', [AuthMiddleware::class]);
$router->post('/patrol-logs/{id}/complete', 'PatrolLogController@complete', [AuthMiddleware::class]);
$router->post('/patrol-logs/{id}/add-incident', 'PatrolLogController@addIncident', [AuthMiddleware::class]);

// Custody Records Management
$router->get('/custody', 'CustodyController@index', [AuthMiddleware::class]);
$router->get('/custody/{id}', 'CustodyController@show', [AuthMiddleware::class]);
$router->post('/custody/store', 'CustodyController@store', [AuthMiddleware::class]);
$router->post('/custody/{id}/release', 'CustodyController@release', [AuthMiddleware::class]);
$router->post('/custody/{id}/log', 'CustodyController@addLog', [AuthMiddleware::class]);

// Court Calendar
$router->get('/court-calendar', 'CourtCalendarController@index', [AuthMiddleware::class]);
$router->get('/court-calendar/upcoming', 'CourtCalendarController@upcoming', [AuthMiddleware::class]);
$router->get('/court-calendar/daily', 'CourtCalendarController@daily', [AuthMiddleware::class]);
$router->post('/court-calendar/{id}/outcome', 'CourtCalendarController@updateOutcome', [AuthMiddleware::class]);
$router->get('/court-calendar/statistics', 'CourtCalendarController@statistics', [AuthMiddleware::class]);

// Warrant Management
$router->get('/warrants', 'WarrantController@index', [AuthMiddleware::class]);
$router->get('/warrants/active', 'WarrantController@active', [AuthMiddleware::class]);
$router->get('/warrants/create', 'WarrantController@create', [AuthMiddleware::class]);
$router->get('/warrants/cases/{id}/suspects', 'WarrantController@getCaseSuspects', [AuthMiddleware::class]);
$router->post('/warrants/store', 'WarrantController@store', [AuthMiddleware::class]);
$router->get('/warrants/view/{id}', 'WarrantController@show', [AuthMiddleware::class]);
$router->get('/warrants/{id}/edit', 'WarrantController@edit', [AuthMiddleware::class]);
$router->post('/warrants/{id}/update', 'WarrantController@update', [AuthMiddleware::class]);
$router->get('/warrants/{id}', 'WarrantController@show', [AuthMiddleware::class]);
$router->post('/warrants/{id}/execute', 'WarrantController@execute', [AuthMiddleware::class]);
$router->post('/warrants/{id}/cancel', 'WarrantController@cancel', [AuthMiddleware::class]);

// Intelligence & Operations (Week 9)
$router->get('/intelligence', 'IntelligenceController@index', [AuthMiddleware::class]);
$router->get('/intelligence/reports', 'IntelligenceController@reports', [AuthMiddleware::class]);
$router->get('/intelligence/reports/create', 'IntelligenceController@createReport', [AuthMiddleware::class]);
$router->post('/intelligence/reports/store', 'IntelligenceController@storeReport', [AuthMiddleware::class]);
$router->get('/intelligence/reports/{id}', 'IntelligenceController@viewReport', [AuthMiddleware::class]);
$router->get('/intelligence/surveillance', 'IntelligenceController@surveillance', [AuthMiddleware::class]);
$router->get('/intelligence/surveillance/create', 'IntelligenceController@createSurveillance', [AuthMiddleware::class]);
$router->post('/intelligence/surveillance/store', 'IntelligenceController@storeSurveillance', [AuthMiddleware::class]);
$router->get('/intelligence/surveillance/{id}', 'IntelligenceController@viewSurveillance', [AuthMiddleware::class]);
$router->get('/intelligence/bulletins', 'IntelligenceController@bulletins', [AuthMiddleware::class]);
$router->get('/intelligence/bulletins/create', 'IntelligenceController@createBulletin', [AuthMiddleware::class]);
$router->post('/intelligence/bulletins/store', 'IntelligenceController@storeBulletin', [AuthMiddleware::class]);
$router->get('/intelligence/tips', 'IntelligenceController@tips', [AuthMiddleware::class]);

// Missing Persons Registry (Week 11)
$router->get('/missing-persons', 'MissingPersonController@index', [AuthMiddleware::class]);
$router->get('/missing-persons/create', 'MissingPersonController@create', [AuthMiddleware::class]);
$router->post('/missing-persons/store', 'MissingPersonController@store', [AuthMiddleware::class]);
$router->get('/missing-persons/{id}', 'MissingPersonController@show', [AuthMiddleware::class]);
$router->get('/missing-persons/{id}/edit', 'MissingPersonController@edit', [AuthMiddleware::class]);
$router->post('/missing-persons/{id}/update', 'MissingPersonController@update', [AuthMiddleware::class]);
$router->get('/missing-persons/{id}/print', 'MissingPersonController@print', [AuthMiddleware::class]);
$router->post('/missing-persons/{id}/link-case', 'MissingPersonController@linkCase', [AuthMiddleware::class]);
$router->post('/missing-persons/{id}/update-status', 'MissingPersonController@updateStatus', [AuthMiddleware::class]);

// Vehicle Registry (Week 11)
$router->get('/vehicles', 'VehicleController@index', [AuthMiddleware::class]);
$router->get('/vehicles/create', 'VehicleController@create', [AuthMiddleware::class]);
$router->post('/vehicles/store', 'VehicleController@store', [AuthMiddleware::class]);
$router->get('/vehicles/{id}', 'VehicleController@show', [AuthMiddleware::class]);
$router->get('/vehicles/search', 'VehicleController@search', [AuthMiddleware::class]);

// Firearms Registry (Week 11)
$router->get('/firearms', 'FirearmController@index', [AuthMiddleware::class]);
$router->get('/firearms/create', 'FirearmController@create', [AuthMiddleware::class]);
$router->post('/firearms/store', 'FirearmController@store', [AuthMiddleware::class]);
$router->get('/firearms/{id}', 'FirearmController@show', [AuthMiddleware::class]);
$router->post('/firearms/{id}/issue', 'FirearmController@issue', [AuthMiddleware::class]);
$router->post('/firearms/{id}/return', 'FirearmController@returnFirearm', [AuthMiddleware::class]);
$router->post('/firearms/{id}/assign', 'FirearmController@assign', [AuthMiddleware::class]);

// Informant Management (Week 9)
$router->get('/informants', 'InformantController@index', [AuthMiddleware::class]);
$router->get('/informants/create', 'InformantController@create', [AuthMiddleware::class]);
$router->post('/informants/store', 'InformantController@store', [AuthMiddleware::class]);
$router->get('/informants/{id}', 'InformantController@show', [AuthMiddleware::class]);
$router->post('/informants/{id}/intelligence', 'InformantController@addIntelligence', [AuthMiddleware::class]);
$router->post('/informants/{id}/status', 'InformantController@updateStatus', [AuthMiddleware::class]);

// Public Tips (Week 9)
$router->get('/submit-tip', 'PublicTipController@create'); // No auth - public form
$router->post('/submit-tip', 'PublicTipController@store'); // No auth - public submission
$router->get('/tips/admin', 'PublicTipController@index', [AuthMiddleware::class]);
$router->get('/tips/{id}', 'PublicTipController@show', [AuthMiddleware::class]);
$router->post('/tips/{id}/assign', 'PublicTipController@assign', [AuthMiddleware::class]);
$router->post('/tips/{id}/status', 'PublicTipController@updateStatus', [AuthMiddleware::class]);

// Data Export (Week 10)
$router->get('/export', 'ExportController@index', [AuthMiddleware::class]);
$router->get('/export/cases', 'ExportController@exportCases', [AuthMiddleware::class]);
$router->get('/export/persons', 'ExportController@exportPersons', [AuthMiddleware::class]);
$router->get('/export/officers', 'ExportController@exportOfficers', [AuthMiddleware::class]);
$router->get('/export/pdf', 'ExportController@exportReportPDF', [AuthMiddleware::class]);

// Reports & Analytics (Week 10)
$router->get('/reports', 'ReportController@index', [AuthMiddleware::class]);
$router->get('/reports/cases', 'ReportController@cases', [AuthMiddleware::class]);
$router->get('/reports/statistics', 'ReportController@statistics', [AuthMiddleware::class]);

// ========================================
// NEW ROUTES FOR 117 COMPONENTS
// ========================================

// Arrests Management
$router->get('/arrests', 'ArrestController@index', [AuthMiddleware::class]);
$router->get('/arrests/view/{id}', 'ArrestController@show', [AuthMiddleware::class]);
$router->get('/arrests/create', 'ArrestController@create', [AuthMiddleware::class]);
$router->post('/arrests/store', 'ArrestController@store', [AuthMiddleware::class]);
$router->get('/arrests/edit/{id}', 'ArrestController@edit', [AuthMiddleware::class]);
$router->post('/arrests/update', 'ArrestController@update', [AuthMiddleware::class]);
$router->post('/arrests/release/{id}', 'ArrestController@release', [AuthMiddleware::class]);

// Bail Management
$router->get('/bail', 'BailController@index', [AuthMiddleware::class]);
$router->get('/bail/view/{id}', 'BailController@show', [AuthMiddleware::class]);
$router->get('/bail/create', 'BailController@create', [AuthMiddleware::class]);
$router->post('/bail/store', 'BailController@store', [AuthMiddleware::class]);
$router->get('/bail/edit/{id}', 'BailController@edit', [AuthMiddleware::class]);
$router->post('/bail/update', 'BailController@update', [AuthMiddleware::class]);
$router->post('/bail/revoke/{id}', 'BailController@revoke', [AuthMiddleware::class]);

// Charges Management
$router->get('/charges', 'ChargeController@index', [AuthMiddleware::class]);
$router->get('/charges/view/{id}', 'ChargeController@show', [AuthMiddleware::class]);
$router->get('/charges/create', 'ChargeController@create', [AuthMiddleware::class]);
$router->post('/charges/store', 'ChargeController@store', [AuthMiddleware::class]);
$router->get('/charges/edit/{id}', 'ChargeController@edit', [AuthMiddleware::class]);
$router->post('/charges/update', 'ChargeController@update', [AuthMiddleware::class]);
$router->post('/charges/withdraw/{id}', 'ChargeController@withdraw', [AuthMiddleware::class]);

// Exhibits Management
$router->get('/exhibits', 'ExhibitController@index', [AuthMiddleware::class]);
$router->get('/exhibits/view/{id}', 'ExhibitController@show', [AuthMiddleware::class]);
$router->get('/exhibits/create', 'ExhibitController@create', [AuthMiddleware::class]);
$router->post('/exhibits/store', 'ExhibitController@store', [AuthMiddleware::class]);
$router->get('/exhibits/edit/{id}', 'ExhibitController@edit', [AuthMiddleware::class]);
$router->post('/exhibits/update', 'ExhibitController@update', [AuthMiddleware::class]);
$router->post('/exhibits/move/{id}', 'ExhibitController@move', [AuthMiddleware::class]);

// Custody Chain Management
$router->get('/custody-chain', 'CustodyChainController@listAll', [AuthMiddleware::class]);

// Operations Management
$router->get('/operations', 'OperationsController@index', [AuthMiddleware::class]);
$router->get('/operations/view/{id}', 'OperationsController@show', [AuthMiddleware::class]);
$router->get('/operations/create', 'OperationsController@create', [AuthMiddleware::class]);
$router->post('/operations/store', 'OperationsController@store', [AuthMiddleware::class]);
$router->get('/operations/edit/{id}', 'OperationsController@edit', [AuthMiddleware::class]);
$router->post('/operations/update', 'OperationsController@update', [AuthMiddleware::class]);
$router->post('/operations/start/{id}', 'OperationsController@start', [AuthMiddleware::class]);
$router->post('/operations/complete/{id}', 'OperationsController@complete', [AuthMiddleware::class]);

// Surveillance Management
$router->get('/surveillance', 'SurveillanceController@index', [AuthMiddleware::class]);
$router->get('/surveillance/view/{id}', 'SurveillanceController@show', [AuthMiddleware::class]);
$router->get('/surveillance/create', 'SurveillanceController@create', [AuthMiddleware::class]);
$router->post('/surveillance/store', 'SurveillanceController@store', [AuthMiddleware::class]);
$router->get('/surveillance/edit/{id}', 'SurveillanceController@edit', [AuthMiddleware::class]);
$router->post('/surveillance/update', 'SurveillanceController@update', [AuthMiddleware::class]);
$router->post('/surveillance/end/{id}', 'SurveillanceController@end', [AuthMiddleware::class]);

// Intelligence Bulletins Management
$router->get('/intelligence/bulletins', 'IntelligenceBulletinController@index', [AuthMiddleware::class]);
$router->get('/intelligence/bulletins/view/{id}', 'IntelligenceBulletinController@show', [AuthMiddleware::class]);
$router->get('/intelligence/bulletins/create', 'IntelligenceBulletinController@create', [AuthMiddleware::class]);
$router->post('/intelligence/bulletins/store', 'IntelligenceBulletinController@store', [AuthMiddleware::class]);
$router->get('/intelligence/bulletins/edit/{id}', 'IntelligenceBulletinController@edit', [AuthMiddleware::class]);
$router->post('/intelligence/bulletins/update', 'IntelligenceBulletinController@update', [AuthMiddleware::class]);
$router->post('/intelligence/bulletins/expire/{id}', 'IntelligenceBulletinController@expire', [AuthMiddleware::class]);
$router->post('/intelligence/bulletins/cancel/{id}', 'IntelligenceBulletinController@cancel', [AuthMiddleware::class]);

// Ammunition Management
$router->get('/ammunition', 'AmmunitionController@index', [AuthMiddleware::class]);
$router->get('/ammunition/create', 'AmmunitionController@create', [AuthMiddleware::class]);
$router->post('/ammunition/store', 'AmmunitionController@store', [AuthMiddleware::class]);
$router->get('/ammunition/edit/{id}', 'AmmunitionController@edit', [AuthMiddleware::class]);
$router->post('/ammunition/update', 'AmmunitionController@update', [AuthMiddleware::class]);
$router->post('/ammunition/restock/{id}', 'AmmunitionController@restock', [AuthMiddleware::class]);
$router->post('/ammunition/issue/{id}', 'AmmunitionController@issue', [AuthMiddleware::class]);
$router->post('/ammunition/allocate/{id}', 'AmmunitionController@allocate', [AuthMiddleware::class]);
$router->get('/ammunition/get-divisions', 'AmmunitionController@getDivisions', [AuthMiddleware::class]);
$router->get('/ammunition/get-districts', 'AmmunitionController@getDistricts', [AuthMiddleware::class]);

// Assets Management
$router->get('/assets', 'AssetController@index', [AuthMiddleware::class]);
$router->get('/assets/view/{id}', 'AssetController@show', [AuthMiddleware::class]);
$router->get('/assets/create', 'AssetController@create', [AuthMiddleware::class]);
$router->post('/assets/store', 'AssetController@store', [AuthMiddleware::class]);
$router->post('/assets/move/{id}', 'AssetController@move', [AuthMiddleware::class]);

// Public Complaints Management
$router->get('/public_complaints', 'PublicComplaintController@index', [AuthMiddleware::class]);
$router->get('/public-complaints', 'PublicComplaintController@index', [AuthMiddleware::class]);
$router->get('/public_complaints/create', 'PublicComplaintController@create', [AuthMiddleware::class]);
$router->get('/public-complaints/create', 'PublicComplaintController@create', [AuthMiddleware::class]);
$router->post('/public_complaints/store', 'PublicComplaintController@store', [AuthMiddleware::class]);
$router->post('/public-complaints/store', 'PublicComplaintController@store', [AuthMiddleware::class]);
$router->get('/public_complaints/view/{id}', 'PublicComplaintController@show', [AuthMiddleware::class]);
$router->get('/public-complaints/{id}', 'PublicComplaintController@show', [AuthMiddleware::class]);
$router->get('/public_complaints/edit/{id}', 'PublicComplaintController@edit', [AuthMiddleware::class]);
$router->get('/public-complaints/{id}/edit', 'PublicComplaintController@edit', [AuthMiddleware::class]);
$router->post('/public_complaints/update', 'PublicComplaintController@update', [AuthMiddleware::class]);
$router->post('/public-complaints/{id}/update', 'PublicComplaintController@update', [AuthMiddleware::class]);
$router->post('/public_complaints/investigate/{id}', 'PublicComplaintController@investigate', [AuthMiddleware::class]);
$router->post('/public-complaints/{id}/investigate', 'PublicComplaintController@investigate', [AuthMiddleware::class]);
$router->post('/public_complaints/resolve/{id}', 'PublicComplaintController@resolve', [AuthMiddleware::class]);
$router->post('/public-complaints/{id}/resolve', 'PublicComplaintController@resolve', [AuthMiddleware::class]);

// Incident Reports Management
$router->get('/incidents', 'IncidentReportController@index', [AuthMiddleware::class]);
$router->get('/incidents/view/{id}', 'IncidentReportController@show', [AuthMiddleware::class]);
$router->get('/incidents/create', 'IncidentReportController@create', [AuthMiddleware::class]);
$router->post('/incidents/store', 'IncidentReportController@store', [AuthMiddleware::class]);
$router->get('/incidents/edit/{id}', 'IncidentReportController@edit', [AuthMiddleware::class]);
$router->post('/incidents/update', 'IncidentReportController@update', [AuthMiddleware::class]);
$router->post('/incidents/escalate/{id}', 'IncidentReportController@escalate', [AuthMiddleware::class]);

// Officer module routes moved to line 93-127 to prevent routing conflicts

// Statements Management
$router->get('/statements', 'StatementController@index', [AuthMiddleware::class]);
$router->get('/statements/view/{id}', 'StatementController@show', [AuthMiddleware::class]);
$router->get('/statements/create', 'StatementController@create', [AuthMiddleware::class]);
$router->post('/statements/store', 'StatementController@store', [AuthMiddleware::class]);
$router->post('/statements/cancel/{id}', 'StatementController@cancel', [AuthMiddleware::class]);
$router->get('/statements/versions/{id}', 'StatementController@versions', [AuthMiddleware::class]);

// Notifications Management
$router->get('/notifications', 'NotificationController@index', [AuthMiddleware::class]);
$router->get('/notifications/unread-count', 'NotificationController@getUnreadCount', [AuthMiddleware::class]);
$router->post('/notifications/mark-read/{id}', 'NotificationController@markAsRead', [AuthMiddleware::class]);
$router->post('/notifications/mark-all-read', 'NotificationController@markAllAsRead', [AuthMiddleware::class]);

// API Routes
$router->get('/api/officers/active', 'OfficerController@getActiveOfficers', [AuthMiddleware::class]);
$router->post('/notifications/delete/{id}', 'NotificationController@delete', [AuthMiddleware::class]);
$router->get('/notifications/recent', 'NotificationController@getRecent', [AuthMiddleware::class]);
