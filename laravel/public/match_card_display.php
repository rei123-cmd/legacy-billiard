<?php
/**
 * ============================================
 * MATCH CARD DISPLAY TEMPLATE
 * ============================================
 */

if (!isset($match)) {
    return;
}

$is_completed = ($match['status'] === 'completed');
$has_players = ($match['player1_id'] && $match['player2_id']);
?>

<div class="match-card <?= $is_completed ? 'completed' : '' ?>">
    <!-- Match Label -->
    <div class="match-label">
        <i class="fas fa-chess-board"></i>
        MATCH #<?= $match['match_number'] ?>
    </div>

    <?php if ($match['created_at']): ?>
        <div class="match-time">
            <i class="far fa-clock"></i>
            <?= date('d M Y, H:i', strtotime($match['created_at'])) ?>
        </div>
    <?php endif; ?>

    <?php if (!$has_players): ?>
        <!-- WAITING FOR PLAYERS -->
        <div class="match-waiting">
            <i class="fas fa-hourglass-half"></i>
            Waiting for players from previous round...
        </div>

    <?php else: ?>
        <!-- PLAYER 1 -->
        <div class="player-row <?= $is_completed && ($match['winner_id'] == $match['player1_id']) ? 'winner' : ($is_completed ? 'loser' : '') ?>">
            <div class="player-info">
                <img src="images/<?= $match['player1_photo'] ?? 'avataruser.jpg' ?>" 
                     alt="Player 1" 
                     class="player-avatar"
                     onerror="this.src='images/avataruser.jpg'">
                <div class="player-details">
                    <div class="player-name"><?= htmlspecialchars($match['player1_name'] ?? 'TBD') ?></div>
                    <div class="player-email"><?= htmlspecialchars($match['player1_email'] ?? '') ?></div>
                </div>
            </div>
            <div class="score-display"><?= $is_completed ? ($match['player1_score'] ?? 0) : '-' ?></div>
        </div>

        <!-- VS TEXT -->
        <div class="vs-text">VS</div>

        <!-- PLAYER 2 -->
        <div class="player-row <?= $is_completed && ($match['winner_id'] == $match['player2_id']) ? 'winner' : ($is_completed ? 'loser' : '') ?>">
            <div class="player-info">
                <img src="images/<?= $match['player2_photo'] ?? 'avataruser.jpg' ?>" 
                     alt="Player 2" 
                     class="player-avatar"
                     onerror="this.src='images/avataruser.jpg'">
                <div class="player-details">
                    <div class="player-name"><?= htmlspecialchars($match['player2_name'] ?? 'TBD') ?></div>
                    <div class="player-email"><?= htmlspecialchars($match['player2_email'] ?? '') ?></div>
                </div>
            </div>
            <div class="score-display"><?= $is_completed ? ($match['player2_score'] ?? 0) : '-' ?></div>
        </div>

        <?php if ($is_completed): ?>
            <!-- MATCH RESULT -->
            <div class="match-result">
                <i class="fas fa-check-circle"></i>
                <strong><?= htmlspecialchars($match['winner_name']) ?></strong> wins!
                <?php if ($match['completed_at']): ?>
                    <br>
                    <small>Completed: <?= date('d M Y, H:i', strtotime($match['completed_at'])) ?></small>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-warning" style="margin-top: 1rem; background: rgba(255, 193, 7, 0.2); border: 2px solid #ffc107; color: #ffc107;">
                <i class="fas fa-clock"></i>
                <strong>Match in progress</strong> - waiting for result
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>
