module.exports = {
    proxy: "http://magepeople.local", // Replace with your local WordPress site URL
    files: [
        "**/*.php",
        "**/*.php",
        "**/*.css",
        "**/*.js",
    ],
    watchOptions: {
        usePolling: true, // Use polling for better compatibility
        interval: 500,    // Polling interval (in milliseconds)
    },
    middleware: function (req, res, next) {
        res.setHeader("Cache-Control", "no-store"); // Disable caching for dynamic files
        next();
    },
    notify: true,  // Show Browsersync notifications
    open: false,   // Prevent automatic browser opening
    logLevel: "debug", // Enable detailed logging for troubleshooting
};
