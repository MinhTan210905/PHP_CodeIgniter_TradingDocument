<?php
$file = 'd:/Student_HCMUE/PHP/htdocs/PHP_CodeIgniter_TradingDocument/application/views/partials/footer.php';
$content = file_get_contents($file);

if (strpos($content, 'syncActionRequiredCount()') === false) {
    // Define syncActionRequiredCount
    $sync_action = "
function syncActionRequiredCount() {
    fetch('<?= site_url(\"orders/ajax_get_action_required_count\") ?>', {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            const count = data.count;
            const actionBadges = document.querySelectorAll('.action-required-badge');
            actionBadges.forEach(badge => {
                if (count > 0) {
                    badge.innerText = count;
                    badge.classList.remove('d-none');
                } else {
                    badge.classList.add('d-none');
                }
            });
        }
    });
}
";
    $content = preg_replace('/(function syncUnreadCount\(\) \{.*?\n\})/s', "$1\n$sync_action", $content);
    
    // Add setInterval and initial call
    $init_calls = "
    syncUnreadCount();
    syncActionRequiredCount();
    setInterval(syncUnreadCount, 15000);
    setInterval(syncActionRequiredCount, 15000);

    // Bind Pusher if available
    if (typeof globalPusher !== 'undefined' && globalPusher) {
        var userChannel = globalPusher.subscribe('user-<?= \$this->session->userdata(\"user_id\") ?>');
        
        // When new message arrives
        userChannel.bind('new-message', function(data) {
            syncUnreadCount();
            if (typeof syncInboxList === 'function') {
                syncInboxList(); // If we are on inbox page
            }
        });

        // When order event arrives (e.g. buyer checkout, seller verify QR)
        userChannel.bind('order-event', function(data) {
            syncActionRequiredCount();
            if (data.message) {
                // Show a toast or alert (optional, let's just use alert for simplicity or nothing if we don't want intrusive)
                // We'll just sync the badge.
            }
        });
    }
";
    $content = preg_replace('/syncUnreadCount\(\);\s*setInterval\(syncUnreadCount, 15000\);/s', $init_calls, $content);
    file_put_contents($file, $content);
    echo "Added syncActionRequiredCount";
} else {
    echo "Already exists";
}
