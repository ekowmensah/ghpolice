<?php

namespace App\Config;

class Constants
{
    /**
     * Application constants
     */
    public const APP_NAME = 'GHPIMS';
    public const APP_VERSION = '1.0.0';
    public const APP_TIMEZONE = 'Africa/Accra';
    
    /**
     * Pagination
     */
    public const ITEMS_PER_PAGE = 25;
    public const MAX_PAGINATION_LINKS = 5;
    
    /**
     * File upload
     */
    public const MAX_FILE_SIZE = 5242880; // 5MB in bytes
    public const ALLOWED_FILE_TYPES = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'gif'];
    public const UPLOAD_PATH = 'uploads/';
    
    /**
     * Case statuses
     */
    public const CASE_STATUSES = [
        'Open',
        'Under Investigation',
        'Closed',
        'Suspended',
        'Transferred'
    ];
    
    /**
     * Case priorities
     */
    public const CASE_PRIORITIES = [
        'Low',
        'Medium',
        'High',
        'Critical'
    ];
    
    /**
     * Case types
     */
    public const CASE_TYPES = [
        'Complaint',
        'Incident Report',
        'Investigation',
        'Intelligence'
    ];
    
    /**
     * Evidence statuses
     */
    public const EVIDENCE_STATUSES = [
        'Collected',
        'In Storage',
        'In Lab',
        'In Court',
        'Returned',
        'Destroyed'
    ];
    
    /**
     * Evidence types
     */
    public const EVIDENCE_TYPES = [
        'Physical',
        'Digital',
        'Documentary',
        'Biological',
        'Testimonial'
    ];
    
    /**
     * Warrant types
     */
    public const WARRANT_TYPES = [
        'Arrest Warrant',
        'Search Warrant',
        'Bench Warrant'
    ];
    
    /**
     * Officer statuses
     */
    public const OFFICER_STATUSES = [
        'Active',
        'On Leave',
        'Suspended',
        'Retired',
        'Deceased'
    ];
    
    /**
     * Station types
     */
    public const STATION_TYPES = [
        'Police Station',
        'Police Post',
        'Divisional Headquarters',
        'Regional Headquarters',
        'National Headquarters'
    ];
    
    /**
     * Risk levels
     */
    public const RISK_LEVELS = [
        'None',
        'Low',
        'Medium',
        'High',
        'Critical'
    ];
    
    /**
     * Alert priorities
     */
    public const ALERT_PRIORITIES = [
        'Low',
        'Medium',
        'High',
        'Critical'
    ];
    
    /**
     * Notification types
     */
    public const NOTIFICATION_TYPES = [
        'case_assignment',
        'task_assignment',
        'case_status',
        'deadline',
        'alert',
        'system'
    ];
    
    /**
     * Date formats
     */
    public const DATE_FORMAT = 'Y-m-d';
    public const DATETIME_FORMAT = 'Y-m-d H:i:s';
    public const DISPLAY_DATE_FORMAT = 'd M Y';
    public const DISPLAY_DATETIME_FORMAT = 'd M Y H:i';
}
