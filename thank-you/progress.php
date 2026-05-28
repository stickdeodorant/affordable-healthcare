<?php 

    session_start();

    $pageName = 'progress';
    include 'inc/header.php';
?>

<section id="hero" class="first-section">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center d-flex flex-column align-items-center justify-content-center py-5">
                <div class="submit-progress"></div>
                <div class="submit-text"></div>
            </div>
        </div>
    </div>
</section>

<?php include "inc/footer.php"; ?>
<script src="../js/jquery-3.2.1.min.js"></script>
<script src="../js/bootstrap.min.js"></script>
<script src="https://cdn.jsdelivr.net/gh/tigrr/circle-progress@v0.2.4/dist/circle-progress.min.js" type="module"></script>

<script type="text/javascript" charset="utf-8">
    $(document).ready(function() {
        
        const cp = new CircleProgress('.submit-progress', {
            value: 0,
            textFormat: 'percent',
            animation: 'easeInOutCubic',
            max: 100,
        })

        let currentValue = 0;
        const duration = 6000; // 6 seconds in milliseconds
        const intervalDuration = 100; // Choosing a small interval for smoother increments
        const totalIntervals = duration / intervalDuration;
        let intervalsPassed = 0;
        let cpText = 'Verifying Your Information';

        const interval = setInterval(function() {
            intervalsPassed++;

            if (currentValue <= 30) {
                console.log(cpText);
                cpText = 'Verifying Your Information';
            }
            if(currentValue > 30 && currentValue < 50) {
                console.log(cpText);
                cpText = 'Submitting Your Application';
            }
            if(currentValue > 50 && currentValue > 85) {
                console.log(cpText);
                cpText = 'Finalizing Application';
            }
            if(currentValue > 85) {
                console.log(cpText);
                cpText = 'Done. Your Information has been received!';
            }

            // If it's the last interval, set the currentValue to 100
            if (intervalsPassed === totalIntervals) {
                currentValue = 100;
                clearInterval(interval);
            } else {
                let maxPossibleIncrement = (100 - currentValue) / (totalIntervals - intervalsPassed);
                let randomIncrement = Math.random() * maxPossibleIncrement;
                currentValue += randomIncrement;
            }

            console.log(Math.floor(currentValue)); // Replace this with your action, e.g., updating an element
            cp.value = Math.floor(currentValue); // Replace this with your action, e.g., updating an element
            $('.submit-text').text(cpText);

        }, intervalDuration);

    })
</script>

</body>
</html>