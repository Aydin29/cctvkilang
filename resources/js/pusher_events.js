// Use the shared Echo instance from bootstrap.js
const dashboardChannel = window.Echo.channel('dashboard');
const cctvChannel = window.Echo.channel('cctv-channel');

// Listen for dashboard statistics updates
dashboardChannel.listen('.stats.updated', (data) => {
    console.log('Dashboard stats updated:', data);
    // Update dashboard statistics in real-time
    updateDashboardStats(data);
});

// Listen for CCTV status updates
dashboardChannel.listen('CctvStatusUpdated', (data) => {
    console.log('CCTV status updated:', data);
    // Update CCTV status in real-time
    updateCctvStatus(data);
});

// Listen for CCTV-specific updates
cctvChannel.listen('cctv-updated', (data) => {
    console.log('CCTV updated:', data);
    // Add your logic to update the UI based on the event
    updateCctvDetails(data);
});

// Function to update dashboard statistics
function updateDashboardStats(stats) {
    // Update total cameras count
    const totalElement = document.querySelector('[data-stat="total"]');
    if (totalElement) totalElement.textContent = stats.total || 0;
    
    // Update online cameras count
    const onlineElement = document.querySelector('[data-stat="online"]');
    if (onlineElement) onlineElement.textContent = stats.online || 0;
    
    // Update offline cameras count
    const offlineElement = document.querySelector('[data-stat="offline"]');
    if (offlineElement) offlineElement.textContent = stats.offline || 0;
    
    // Update maintenance cameras count
    const maintenanceElement = document.querySelector('[data-stat="maintenance"]');
    if (maintenanceElement) maintenanceElement.textContent = stats.maintenance || 0;
    
    // Update streaming stats if available
    if (stats.active !== undefined) {
        const activeElement = document.querySelector('[data-stat="active-streams"]');
        if (activeElement) activeElement.textContent = stats.active || 0;
    }
}

// Function to update CCTV status
function updateCctvStatus(data) {
    // This would update the CCTV list with new status information
    console.log('Updating CCTV status with:', data);
    // Implement specific UI updates based on the data received
}

// Function to update CCTV details
function updateCctvDetails(data) {
    // This would update specific CCTV details
    console.log('Updating CCTV details with:', data);
    // Implement specific UI updates based on the data received
}

// Initialize real-time updates
document.addEventListener('DOMContentLoaded', function() {
    console.log('Pusher events initialized');
});
