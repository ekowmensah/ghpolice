// Custom JavaScript for GHPIMS

$(document).ready(function() {
    
    // Show loading spinner on form submit
    $('form').on('submit', function() {
        showSpinner();
    });
    
    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
    
    // Confirm delete actions
    $('.btn-delete').on('click', function(e) {
        if (!confirm('Are you sure you want to delete this item?')) {
            e.preventDefault();
        }
    });
    
    // Crime check form
    $('#crime-check-form').on('submit', function(e) {
        e.preventDefault();
        
        showSpinner();
        
        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                hideSpinner();
                displayCrimeCheckResults(response);
            },
            error: function() {
                hideSpinner();
                alert('Error performing crime check. Please try again.');
            }
        });
    });
    
    // Person search autocomplete
    $('#person-search').on('keyup', function() {
        var keyword = $(this).val();
        
        if (keyword.length < 3) {
            $('#search-results').hide();
            return;
        }
        
        $.ajax({
            url: '/persons/search',
            method: 'GET',
            data: { q: keyword },
            success: function(response) {
                displaySearchResults(response);
            }
        });
    });
    
});

function showSpinner() {
    $('.spinner-overlay').addClass('active');
}

function hideSpinner() {
    $('.spinner-overlay').removeClass('active');
}

function displayCrimeCheckResults(data) {
    var resultsHtml = '';
    
    if (data.found) {
        resultsHtml += '<div class="alert alert-warning">';
        resultsHtml += '<h4><i class="fas fa-exclamation-triangle"></i> Person Found in System</h4>';
        resultsHtml += '<p><strong>Name:</strong> ' + data.person.full_name + '</p>';
        
        if (data.person.has_criminal_record) {
            resultsHtml += '<div class="alert alert-criminal-record mt-2">';
            resultsHtml += '<strong>WARNING: HAS CRIMINAL RECORD</strong>';
            resultsHtml += '</div>';
        }
        
        if (data.person.is_wanted) {
            resultsHtml += '<div class="alert alert-wanted mt-2">';
            resultsHtml += '<strong>ALERT: PERSON IS WANTED</strong>';
            resultsHtml += '</div>';
        }
        
        resultsHtml += '</div>';
    } else {
        resultsHtml += '<div class="alert alert-info">';
        resultsHtml += '<p>Person not found in system</p>';
        resultsHtml += '</div>';
    }
    
    $('#crime-check-results').html(resultsHtml);
}

function displaySearchResults(data) {
    var resultsHtml = '<ul class="list-group">';
    
    if (data.length > 0) {
        data.forEach(function(person) {
            resultsHtml += '<li class="list-group-item">';
            resultsHtml += '<a href="/persons/' + person.id + '">' + person.full_name + '</a>';
            if (person.is_wanted) {
                resultsHtml += ' <span class="badge badge-danger">WANTED</span>';
            }
            resultsHtml += '</li>';
        });
    } else {
        resultsHtml += '<li class="list-group-item">No results found</li>';
    }
    
    resultsHtml += '</ul>';
    $('#search-results').html(resultsHtml).show();
}

// DataTables initialization
if ($.fn.DataTable) {
    $('.data-table').DataTable({
        responsive: true,
        pageLength: 25,
        order: [[0, 'desc']]
    });
}
