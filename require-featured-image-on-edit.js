class RequireFeaturedImage {
    constructor(passedFromServer) {
        this.passedFromServer = passedFromServer;
        this.imageSizeCheckTrials = [true, true, true];
        this.init();
    }

    // Initialize the functionality
    init() {
        this.detectWarnFeaturedImage();
        setInterval(() => this.detectWarnFeaturedImage(), 800);
    }

    // Check if Gutenberg editor is active
    isGutenberg() {
        return jQuery('.block-editor-writing-flow').length > 0;
    }

    // Returns a warning message if there's no featured image or it's too small
    getWarningMessage() {
        const $img = this.isGutenberg()
            ? jQuery('.editor-post-featured-image img')
            : jQuery('#postimagediv img');

        if ($img.length === 0) {
            return this.passedFromServer.jsWarningHtml;
        }

        if (this.isFeaturedImageTooSmall($img)) {
            return this.passedFromServer.jsSmallHtml;
        }

        return '';
    }

    // Check if the featured image is too small
    isFeaturedImageTooSmall($img) {
        if (this.imageSizeCheckTrials.length > 2) {
            this.imageSizeCheckTrials.shift();
        }

        this.imageSizeCheckTrials.push(this.isImageSizeInvalid($img));

        const failureCount = this.imageSizeCheckTrials.reduce((a, b) => a + b, 0);
        return failureCount > 2;
    }

    // Check if the image dimensions are below the required size
    isImageSizeInvalid($img) {
        if (!$img || !$img[0]) return true;

        const imageSrc = $img[0].src;
        const cleanSrc = imageSrc.replace(/-\d+[Xx]\d+\./g, '.');
        const featuredImage = new Image();
        featuredImage.src = cleanSrc;

        return (
            featuredImage.width < this.passedFromServer.width ||
            featuredImage.height < this.passedFromServer.height
        );
    }

    // Display a warning and disable the publish button
    disablePublishWithWarning(message) {
        this.createMessageArea();

        jQuery('#nofeature-message')
            .addClass('error')
            .html(`<p>${message}</p>`);

        const publishButton = this.isGutenberg()
            ? jQuery('.editor-post-publish-panel__toggle')
            : jQuery('#publish');

        publishButton.attr('disabled', 'disabled');
    }

    // Clear warning and enable the publish button
    enablePublish() {
        jQuery('#nofeature-message').remove();

        const publishButton = this.isGutenberg()
            ? jQuery('.editor-post-publish-panel__toggle')
            : jQuery('#publish');

        publishButton.removeAttr('disabled');
    }

    // Create a message area if it doesn't exist
    createMessageArea() {
        if (jQuery('#nofeature-message').length === 0) {
            const container = this.isGutenberg()
                ? jQuery('.components-notice-list')
                : jQuery('#post');

            container.before('<div id="nofeature-message"></div>');
        }
    }

    // Check for warnings and handle the publish button state
    detectWarnFeaturedImage() {
        const message = this.getWarningMessage();

        if (message) {
            this.disablePublishWithWarning(message);
        } else {
            this.enablePublish();
        }
    }
}

// Initialize the class with data passed from the server
jQuery(document).ready(function () {
    const requireFeaturedImage = new RequireFeaturedImage(passedFromServer);
});
