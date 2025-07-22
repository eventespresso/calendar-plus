const ECPDM = (function () {

    let backupConfirmed = null;
    let completeHeader = null;
    let completeMessage = null;
    let continueButton = null;
    let dbVersions = null;
    let errorsHeader = null;
    let errorsList = null;
    let errorsMessage = null;
    let feedbackList = null;
    let feedbackWrapper = null;
    let inProgressHeader = null;
    let inProgressMessage = null;
    let lastJob = null;
    let migrationStatus = null;
    let nonce = null;
    let percent = null;
    let progressBar = null;
    let progressContainer = null;
    let requiredContainer = null;
    let resetButton = null;
    let updateDbButton = null;


    function init() {

        backupConfirmed = document.getElementById("db-backup-confirmed");
        completeHeader = document.getElementById("migrations-complete-hdr");
        completeMessage = document.getElementById("migration-complete-message");
        continueButton = document.getElementById("restart_migrations");
        dbVersions = document.querySelectorAll(".db-schema-version");
        errorsHeader = document.getElementById("migration-errors-hdr");
        errorsList = document.getElementById("migration-errors");
        errorsMessage = document.getElementById("migration-errors-message");
        feedbackList = document.getElementById("migration-feedback");
        feedbackWrapper = document.querySelector(".feedback-wrapper");
        inProgressHeader = document.getElementById("migrations-in-progress-hdr");
        inProgressMessage = document.getElementById("migration-in-progress-message");
        lastJob = document.getElementById("last-migration-job");
        migrationStatus = document.getElementById("migration-status");
        nonce = document.getElementById("migrations-nonce");
        percent = document.getElementById("percent");
        progressBar = document.getElementById("progress-bar-fill");
        progressContainer = document.getElementById("migrations-in-progress");
        requiredContainer = document.getElementById("migrations-required");
        resetButton = document.getElementById("reset_migrations");
        updateDbButton = document.getElementById("update-db");

        if (! backupConfirmed || ! updateDbButton) {
            console.warn("Required elements not found: #db-backup-confirmed or #update-db");
            return;
        }

        addEventListeners();
        // Enable/disable the #update-db button based on #db-backup-confirmed checkbox
        toggleUpdateDbButton();
        migrationsInProgress();
    }

    function addEventListeners() {
        backupConfirmed.addEventListener("change", toggleUpdateDbButton);
        updateDbButton.addEventListener("click", runMigrations);
        continueButton.addEventListener("click", continueMigrations);
        resetButton.addEventListener("click", resetMigrations);
    }

    function toggleUpdateDbButton() {
        updateDbButton.toggleAttribute("disabled", ! backupConfirmed.checked);
    }

    function runMigrations(event) {
        if (! backupConfirmed.checked) {
            event.preventDefault();
            alert("Please confirm the database backup before proceeding.");
            return;
        }
        switchActiveContainer(requiredContainer, progressContainer);
        const data = new FormData();
        data.append("action", "events_calendar_plus_migrations");
        data.append("migration_job", '');
        data.append("restart", false);
        sendAjaxRequest(data, processMigrationResponse);
    }

    function continueMigrations(event) {
        event.preventDefault();
        errorsHeader.style.display = "none";
        errorsMessage.style.display = "none";
        errorsList.style.display = "none";
        continueButton.style.display = "none";
        continueButton.setAttribute("disabled", "disabled");
        const data = new FormData();
        data.append("action", "events_calendar_plus_migrations");
        data.append("migration_job", lastJob.value);
        data.append("restart", true);
        sendAjaxRequest(data, processMigrationResponse);
    }

    function migrationsInProgress() {
        if (migrationStatus.value === "in-progress") {
            updateDbButton.setAttribute("disabled", "disabled");
            backupConfirmed.setAttribute("disabled", "disabled");
        }
    }

    function resetMigrations(event) {
        event.preventDefault();
        const data = new FormData();
        data.append("action", "events_calendar_plus_reset_migrations");
        data.append("nonce", nonce.value);
        sendAjaxRequest(data, processResetResponse);
    }

    function switchActiveContainer(currentContainer, newContainer) {
        currentContainer.classList.add("hide-container");
        newContainer.classList.remove("hide-container");
    }

    function completeMigrations() {
        inProgressHeader.style.display = "none";
        inProgressMessage.style.display = "none";
        completeHeader.style.display = "block";
        completeMessage.style.display = "block";
    }

    function updateProgressBar(processed, totalItems) {
        const progress = totalItems ? (processed / totalItems) * 100 : 0;
        progressBar.style.width = `${progress}%`;
        percent.textContent = ` ${Math.round(progress)}% (${processed} of ${totalItems})`;
        return progress;
    }

    function updateDatabaseVersionProgress(current_db, completed = false) {
        dbVersions.forEach((version) => {
            version.classList.remove("completed", "in-progress");
            if (version.id === `db-schema-version-${current_db}`) {
                version.classList.add("in-progress");
            } else if (parseInt(version.id.split("-").pop()) < current_db) {
                version.classList.add("completed");
            }
            if (completed) {
                version.classList.remove("in-progress");
                version.classList.add("completed");
            }
        });
    }

    function sendAjaxRequest(data, responseCallback) {
        data.append("nonce", nonce.value);
        fetch(ajaxurl, {method: "POST", body: data})
            .then((response) => response.json())
            .then((data) => {
                console.log("%c sendAjaxRequest() response:", "color: Cyan;", data);
                responseCallback(data);
            })
            .catch((error) => {
                console.error("Error:", error);
                alert("An unknown error occurred while updating the database.");
            });
    }

    function processMigrationResponse(response) {
        if (response?.newNonce) {
            nonce.value = response.newNonce;
        }
        if (response?.errors && Array.isArray(response.errors) && response.errors.length) {
            processErrors(response.errors);
        }
        if (response?.lastJob) {
            lastJob.value = response.lastJob;
        }
        if (response.success) {
            if (response.progress && Array.isArray(response.progress) && response.progress.length) {
                processFeedback(response);
            }

            if (response?.current_db) {
                updateDatabaseVersionProgress(response.current_db, response?.completed);
            }

            if (response?.nextJob) {
                const data = new FormData();
                data.append("action", "events_calendar_plus_migrations");
                data.append("migration_job", response.nextJob);
                data.append("restart", false);
                sendAjaxRequest(data, processMigrationResponse);
            } else if (response?.completed) {
                completeMigrations();
            }
        }
    }

    function processFeedback(data) {
        data.progress.forEach((result) => {
            const listItem = document.createElement("li");
            listItem.innerHTML = result;
            feedbackList.appendChild(listItem);
            // Scroll to bottom after adding all new items
            feedbackWrapper.scrollTop = feedbackWrapper.scrollHeight;
        });
        updateProgressBar(
            data?.processed || 0,
            data?.totalItems || 0
        );
    }

    function processErrors(errors) {
        alert("An error occurred while updating the database.");
        console.error("Error updating database: ", errors);
        errorsHeader.style.display = "block";
        errorsMessage.style.display = "block";
        errorsList.innerHTML = "";
        errorsList.style.display = "block";
        if (Array.isArray(errors)) {
            errors.forEach((result) => {
                const listItem = document.createElement("li");
                listItem.classList.add("migration-error");
                listItem.innerHTML = result;
                errorsList.appendChild(listItem);
            });
        }
        continueButton.style.display = "block";
        continueButton.removeAttribute("disabled");
    }

    function processResetResponse(data) {
        if (data.success) {
            window.location.href =
                "admin.php?page=events-calendar-plus-migrations";
        }
    }

    return {init};
})();

document.addEventListener("DOMContentLoaded", ECPDM.init);
