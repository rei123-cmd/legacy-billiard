<?php
/**
 * ══════════════════════════════════════════════════════════════
 * MATCH CARD TEMPLATE - USER VIEW (READ-ONLY)
 * ══════════════════════════════════════════════════════════════
 */

if (!isset($match)) {
    return;
}

$is_completed = ($match['status'] === 'completed');
$has_players = ($match['player1_id'] && $match['player2_id']);
$is_waiting = !$has_players;
$is_live = (!$is_completed && $has_players);
?>

<div class="match-ultra <?= $is_completed ? 'completed' : '' ?> <?= $is_live ? 'live' : '' ?>">
    <!-- Match Label -->
    <div class="match-label">
        <i class="fas fa-chess-board"></i>
        MATCH #<?= $match['match_number'] ?>
    </div>

    <?php if ($match['created_at']): ?>
        <div class="match-time">
            <i class="far fa-clock"></i>
            <?= date('d M Y, H:i', strtotime($match['created_at'])) ?> WIB
        </div>
    <?php endif; ?>

    <?php if ($is_waiting): ?>
        <!-- WAITING FOR PLAYERS -->
        <div class="match-waiting">
            <div class="waiting-icon"><i class="fas fa-hourglass-half"></i></div>
            <div class="waiting-text">Waiting for players from previous round...</div>
        </div>

    <?php elseif ($is_live): ?>
        <!-- MATCH IN PROGRESS -->
        <div class="live-badge">
            <i class="fas fa-play-circle"></i>
            MATCH IN PROGRESS - ADMIN SETTING WINNER
        </div>

        <!-- Player 1 -->
        <div class="player-row">
            <div class="player-info">
                <img src="images/<?= $match['player1_photo'] ?? 'avataruser.jpg' ?>" alt="Player 1" class="player-avatar" onerror="this.src='images/avataruser.jpg'">
                <div class="player-details">
                    <div class="player-name"><?= htmlspecialchars($match['player1_name'] ?? 'TBD') ?></div>
                    <div class="player-email"><?= htmlspecialchars($match['player1_email'] ?? '') ?></div>
                </div>
            </div>
            <div class="score-display">-</div>
        </div>

        <!-- VS -->
        <div class="vs-section">
            <div class="vs-text">VS</div>
        </div>

        <!-- Player 2 -->
        <div class="player-row">
            <div class="player-info">
                <img src="images/<?= $match['player2_photo'] ?? 'avataruser.jpg' ?>" alt="Player 2" class="player-avatar" onerror="this.src='images/avataruser.jpg'">
                <div class="player-details">
                    <div class="player-name"><?= htmlspecialchars($match['player2_name'] ?? 'TBD') ?></div>
                    <div class="player-email"><?= htmlspecialchars($match['player2_email'] ?? '') ?></div>
                </div>
            </div>
            <div class="score-display">-</div>
        </div>

    <?php else: ?>
        <!-- MATCH COMPLETED -->

        <!-- Player 1 -->
        <div class="player-row <?= ($match['winner_id'] == $match['player1_id']) ? 'winner' : 'loser' ?>">
            <div class="player-info">
                <img src="images/<?= $match['player1_photo'] ?? 'avataruser.jpg' ?>" alt="Player 1" class="player-avatar" onerror="this.src='images/avataruser.jpg'">
                <div class="player-details">
                    <div class="player-name"><?= htmlspecialchars($match['player1_name'] ?? 'TBD') ?></div>
                    <div class="player-email"><?= htmlspecialchars($match['player1_email'] ?? '') ?></div>
                </div>
            </div>
            <div class="score-display"><?= $match['player1_score'] ?? 0 ?></div>
        </div>

        <!-- VS -->
        <div class="vs-section">
            <div class="vs-text">VS</div>
        </div>

        <!-- Player 2 -->
        <div class="player-row <?= ($match['winner_id'] == $match['player2_id']) ? 'winner' : 'loser' ?>">
            <div class="player-info">
                <img src="images/<?= $match['player2_photo'] ?? 'avataruser.jpg' ?>" alt="Player 2" class="player-avatar" onerror="this.src='images/avataruser.jpg'">
                <div class="player-details">
                    <div class="player-name"><?= htmlspecialchars($match['player2_name'] ?? 'TBD') ?></div>
                    <div class="player-email"><?= htmlspecialchars($match['player2_email'] ?? '') ?></div>
                </div>
            </div>
            <div class="score-display"><?= $match['player2_score'] ?? 0 ?></div>
        </div>

        <!-- Match Result -->
        <div class="match-result">
            <div class="result-title">
                <i class="fas fa-check-circle"></i>
                <?= htmlspecialchars($match['winner_name']) ?> WINS!
            </div>
            <?php if ($match['completed_at']): ?>
                <div class="result-time">Completed: <?= date('d M Y, H:i', strtotime($match['completed_at'])) ?> WIB</div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
