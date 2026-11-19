// Movya - Client-side logic (jQuery + AJAX)
var allCurrentItems = [];
var activeFeed = "popular";

$(document).ready(function() {

    // 1. Handle Search Submission
    $("#search-form").on("submit", function(e) {
        e.preventDefault();

        var query = $("#query-input").val().trim();
        var source = $("#source-select").val();

        if (query === "") {
            return;
        }

        $("#results-grid").empty();
        $("#status-msg").text("Searching for \"" + query + "\"...");
        allCurrentItems = [];

        var requestsPending = 0;

        // Query TVmaze
        if (source === "all" || source === "tvmaze") {
            requestsPending++;
            $.ajax({
                url: "https://api.tvmaze.com/search/shows?q=" + encodeURIComponent(query),
                method: "GET",
                dataType: "json",
                success: function(data) {
                    for (var i = 0; i < data.length; i++) {
                        var item = data[i].show;
                        var year = item.premiered ? item.premiered.substring(0, 4) : "";
                        var poster = item.image && item.image.medium ? item.image.medium : "";
                        var rating = item.rating && item.rating.average ? parseFloat(item.rating.average) : 0;
                        var isOtt = (item.webChannel && item.webChannel.name) ? true : false;

                        allCurrentItems.push({
                            source: "TVmaze",
                            id: item.id,
                            title: item.name,
                            year: year,
                            type: "series",
                            rating: rating,
                            isOtt: isOtt,
                            poster: poster
                        });
                    }
                },
                complete: function() {
                    requestsPending--;
                    if (requestsPending <= 0) {
                        applyDiscoveryFilters();
                    }
                }
            });
        }

        // Query OMDb (using standard student public key)
        if (source === "all" || source === "omdb") {
            requestsPending++;
            var omdbKey = window.MOVYA_OMDB_API_KEY || "";
            $.ajax({
                url: "https://www.omdbapi.com/?apikey=" + omdbKey + "&s=" + encodeURIComponent(query),
                method: "GET",
                dataType: "json",
                success: function(data) {
                    if (data.Response === "True" && data.Search) {
                        for (var i = 0; i < data.Search.length; i++) {
                            var movie = data.Search[i];
                            var year = movie.Year ? movie.Year.substring(0, 4) : "";
                            var poster = movie.Poster !== "N/A" ? movie.Poster : "";
                            var type = movie.Type === "series" ? "series" : "movie";

                            allCurrentItems.push({
                                source: "OMDb",
                                id: movie.imdbID,
                                title: movie.Title,
                                year: year,
                                type: type,
                                rating: 0, // OMDb search endpoint does not return rating; verified on details page
                                isOtt: false,
                                poster: poster
                            });
                        }
                    }
                },
                complete: function() {
                    requestsPending--;
                    if (requestsPending <= 0) {
                        applyDiscoveryFilters();
                    }
                }
            });
        }
    });

    // 2. Toolbar feed buttons (Popular vs Trending)
    $(".filter-feed-btn").on("click", function() {
        $(".filter-feed-btn").removeClass("btn-primary").addClass("btn-outline-secondary");
        $(this).removeClass("btn-outline-secondary").addClass("btn-primary");
        activeFeed = $(this).data("feed");
        loadDiscoveryFeed(activeFeed);
    });

    // 3. Filter dropdowns change
    $("#filter-year, #filter-type, #filter-rating").on("change", function() {
        applyDiscoveryFilters();
    });

    // 4. Reset Filters button
    $("#btn-reset-filters").on("click", function() {
        $("#filter-year").val("");
        $("#filter-type").val("all");
        $("#filter-rating").val("0");
        applyDiscoveryFilters();
    });

    // 5. Add to Favorites click handler (delegated)
    $(document).on("click", ".add-fav-btn", function(e) {
        e.preventDefault();
        e.stopPropagation();

        var btn = $(this);
        var source = btn.data("source");
        var extId = btn.data("id");
        var title = btn.data("title");
        var year = btn.data("year");
        var poster = btn.data("poster");

        $.ajax({
            url: "add_favorite.php",
            method: "POST",
            dataType: "json",
            data: {
                source: source,
                external_id: extId,
                title: title,
                year: year,
                poster: poster
            },
            success: function(res) {
                if (res.status === "success") {
                    btn.removeClass("btn-outline-warning btn-warning").addClass("btn-success").text("✓ Saved");
                } else if (res.status === "exists") {
                    alert("Already in your favorites!");
                } else {
                    alert(res.message);
                }
            },
            error: function() {
                alert("Please login first to save favorites.");
            }
        });
    });
});

// Load Discovery Feed for Homepage
function loadDiscoveryFeed(feed) {
    $("#results-grid").empty();
    $("#status-msg").text("Loading " + (feed === "trending" ? "top rated" : "popular") + " discovery titles...");
    allCurrentItems = [];

    // Combine popular TV series from TVmaze and movies from OMDb
    var queryList = (feed === "trending") 
        ? ["Breaking Bad", "Game of Thrones", "Interstellar", "Dark", "Inception"]
        : ["Batman", "Avengers", "Stranger Things", "Sherlock", "Spider-Man"];

    var completed = 0;

    for (var i = 0; i < queryList.length; i++) {
        var q = queryList[i];

        // TVmaze request
        $.ajax({
            url: "https://api.tvmaze.com/search/shows?q=" + encodeURIComponent(q),
            method: "GET",
            dataType: "json",
            success: function(data) {
                for (var j = 0; j < Math.min(data.length, 2); j++) {
                    var show = data[j].show;
                    allCurrentItems.push({
                        source: "TVmaze",
                        id: show.id,
                        title: show.name,
                        year: show.premiered ? show.premiered.substring(0, 4) : "",
                        type: "series",
                        rating: show.rating && show.rating.average ? parseFloat(show.rating.average) : 0,
                        isOtt: (show.webChannel && show.webChannel.name) ? true : false,
                        poster: show.image && show.image.medium ? show.image.medium : ""
                    });
                }
            },
            complete: function() {
                completed++;
                if (completed >= queryList.length * 2) {
                    applyDiscoveryFilters();
                }
            }
        });

        // OMDb request
        $.ajax({
            url: "https://www.omdbapi.com/?apikey=" + encodeURIComponent(window.MOVYA_OMDB_API_KEY || "") + "&s=" + encodeURIComponent(q) + "&type=movie",
            method: "GET",
            dataType: "json",
            success: function(data) {
                if (data.Response === "True" && data.Search) {
                    for (var k = 0; k < Math.min(data.Search.length, 2); k++) {
                        var m = data.Search[k];
                        allCurrentItems.push({
                            source: "OMDb",
                            id: m.imdbID,
                            title: m.Title,
                            year: m.Year ? m.Year.substring(0, 4) : "",
                            type: "movie",
                            rating: 0,
                            isOtt: false,
                            poster: m.Poster !== "N/A" ? m.Poster : ""
                        });
                    }
                }
            },
            complete: function() {
                completed++;
                if (completed >= queryList.length * 2) {
                    applyDiscoveryFilters();
                }
            }
        });
    }
}

// Apply discovery toolbar filters together
function applyDiscoveryFilters() {
    var yearFilter = $("#filter-year").val();
    var typeFilter = $("#filter-type").val();
    var ratingFilter = parseFloat($("#filter-rating").val()) || 0;

    var filtered = allCurrentItems.filter(function(item) {
        // Year filter check
        if (yearFilter !== "") {
            var itemYear = parseInt(item.year, 10);
            if (isNaN(itemYear)) return false;

            if (yearFilter === "classic") {
                if (itemYear >= 2000) return false;
            } else if (yearFilter === "2015") {
                if (itemYear < 2015 || itemYear > 2019) return false;
            } else if (yearFilter === "2010") {
                if (itemYear < 2010 || itemYear > 2014) return false;
            } else if (yearFilter === "2000") {
                if (itemYear < 2000 || itemYear > 2009) return false;
            } else {
                if (itemYear !== parseInt(yearFilter, 10)) return false;
            }
        }

        // Content Type check
        if (typeFilter === "movie" && item.type !== "movie") return false;
        if (typeFilter === "series" && item.type !== "series") return false;
        if (typeFilter === "ott" && !item.isOtt) return false;

        // Minimum Rating check (only for items with known ratings)
        if (ratingFilter > 0) {
            if (item.rating < ratingFilter) return false;
        }

        return true;
    });

    // Update active summary text
    var summaryText = "Showing: " + (activeFeed === "trending" ? "Trending" : "Popular");
    if (yearFilter) summaryText += " • Year: " + yearFilter;
    if (typeFilter !== "all") summaryText += " • " + (typeFilter === "ott" ? "OTT/Streaming" : typeFilter.toUpperCase());
    if (ratingFilter > 0) summaryText += " • " + ratingFilter + "+ Rating";
    $("#active-filter-summary").text(summaryText);

    renderContentGrid(filtered);
}

// Render movie / show cards
function renderContentGrid(items) {
    $("#results-grid").empty();
    $("#status-msg").text("");

    if (!items || items.length === 0) {
        $("#results-grid").html(
            '<div class="col-12 text-center text-secondary py-5">' +
                '<h5>No titles found matching the active filters.</h5>' +
                '<p>Try resetting filters or searching with a different keyword.</p>' +
            '</div>'
        );
        return;
    }

    // Render cards
    for (var i = 0; i < items.length; i++) {
        var it = items[i];
        var detailUrl = "details.php?source=" + encodeURIComponent(it.source.toLowerCase()) + "&id=" + encodeURIComponent(it.id);

        var posterHtml = it.poster 
            ? '<img src="' + it.poster + '" class="card-img-top movie-poster" alt="' + it.title.replace(/"/g, '&quot;') + '">'
            : '<div class="movie-poster-placeholder"><span>No Poster</span></div>';

        var ratingBadge = it.rating > 0 
            ? '<span class="text-warning small ms-auto fw-bold">★ ' + it.rating.toFixed(1) + '</span>'
            : '';

        var ottBadge = it.isOtt ? '<span class="badge bg-info text-dark me-1">OTT</span>' : '';

        var cardHtml = 
            '<div class="col-6 col-md-4 col-lg-3">' +
                '<div class="card h-100">' +
                    '<a href="' + detailUrl + '" class="text-decoration-none">' + posterHtml + '</a>' +
                    '<div class="card-body d-flex flex-column">' +
                        '<div class="d-flex align-items-center mb-1">' +
                            '<span class="badge bg-secondary me-1">' + it.source + '</span>' +
                            ottBadge +
                            ratingBadge +
                        '</div>' +
                        '<h6 class="card-title text-truncate mb-1">' +
                            '<a href="' + detailUrl + '" class="text-white text-decoration-none">' + it.title + '</a>' +
                        '</h6>' +
                        '<p class="card-text text-secondary small mb-2">' + (it.year ? 'Year: ' + it.year : 'Type: ' + it.type) + '</p>' +
                        '<div class="mt-auto d-flex gap-2">' +
                            '<a href="' + detailUrl + '" class="btn btn-sm btn-outline-info flex-grow-1">Details</a>' +
                            '<button class="btn btn-sm btn-outline-warning add-fav-btn" ' +
                                'data-source="' + it.source + '" ' +
                                'data-id="' + it.id + '" ' +
                                'data-title="' + it.title.replace(/"/g, '&quot;') + '" ' +
                                'data-year="' + it.year + '" ' +
                                'data-poster="' + it.poster + '">⭐</button>' +
                        '</div>' +
                    '</div>' +
                '</div>' +
            '</div>';

        $("#results-grid").append(cardHtml);
    }
}

// 6. Genre Page Discovery & Shuffle
function loadGenreContent(genreName, contentType, shuffle) {
    $("#genre-results").empty();
    $("#genre-status").text("Loading titles for " + genreName + "...");

    // TVmaze query
    var tvQuery = genreName.toLowerCase();
    if (genreName === "Science Fiction") tvQuery = "sci-fi";
    if (genreName === "Superhero") tvQuery = "superhero";
    if (genreName === "Anime") tvQuery = "anime";

    var results = [];
    var pending = 0;

    // Call TVmaze unless "movie" only
    if (contentType !== "movie") {
        pending++;
        $.ajax({
            url: "https://api.tvmaze.com/search/shows?q=" + encodeURIComponent(tvQuery),
            method: "GET",
            dataType: "json",
            success: function(data) {
                for (var i = 0; i < data.length; i++) {
                    var s = data[i].show;
                    results.push({
                        source: "TVmaze",
                        id: s.id,
                        title: s.name,
                        year: s.premiered ? s.premiered.substring(0, 4) : "N/A",
                        type: "series",
                        rating: s.rating && s.rating.average ? parseFloat(s.rating.average) : 0,
                        poster: s.image && s.image.medium ? s.image.medium : ""
                    });
                }
            },
            complete: function() {
                pending--;
                if (pending <= 0) finalizeGenreDisplay(results, shuffle, genreName);
            }
        });
    }

    // Call OMDb unless "series" only
    if (contentType !== "series") {
        pending++;
        var omdbTerm = genreName;
        if (genreName === "Psychological Thriller") omdbTerm = "Thriller";
        if (genreName === "Mystery & Suspense") omdbTerm = "Suspense";

        $.ajax({
            url: "https://www.omdbapi.com/?apikey=" + encodeURIComponent(window.MOVYA_OMDB_API_KEY || "") + "&s=" + encodeURIComponent(omdbTerm) + "&type=movie",
            method: "GET",
            dataType: "json",
            success: function(data) {
                if (data.Response === "True" && data.Search) {
                    for (var j = 0; j < data.Search.length; j++) {
                        var m = data.Search[j];
                        results.push({
                            source: "OMDb",
                            id: m.imdbID,
                            title: m.Title,
                            year: m.Year ? m.Year.substring(0, 4) : "N/A",
                            type: "movie",
                            rating: 0,
                            poster: m.Poster !== "N/A" ? m.Poster : ""
                        });
                    }
                }
            },
            complete: function() {
                pending--;
                if (pending <= 0) finalizeGenreDisplay(results, shuffle, genreName);
            }
        });
    }
}

function finalizeGenreDisplay(items, shuffle, genreName) {
    $("#genre-status").text("");

    if (shuffle && items.length > 0) {
        // Fisher-Yates shuffle
        for (var i = items.length - 1; i > 0; i--) {
            var j = Math.floor(Math.random() * (i + 1));
            var temp = items[i];
            items[i] = items[j];
            items[j] = temp;
        }
    }

    if (items.length === 0) {
        $("#genre-results").html(
            '<div class="col-12 text-center text-secondary py-5">' +
                '<h5>No results found for ' + genreName + '.</h5>' +
                '<p>Try changing the content type or selecting another genre.</p>' +
            '</div>'
        );
        return;
    }

    for (var k = 0; k < items.length; k++) {
        var it = items[k];
        var detailUrl = "details.php?source=" + encodeURIComponent(it.source.toLowerCase()) + "&id=" + encodeURIComponent(it.id);

        var posterHtml = it.poster 
            ? '<img src="' + it.poster + '" class="card-img-top movie-poster" alt="' + it.title.replace(/"/g, '&quot;') + '">'
            : '<div class="movie-poster-placeholder"><span>No Poster</span></div>';

        var cardHtml = 
            '<div class="col-6 col-md-4 col-lg-3">' +
                '<div class="card h-100">' +
                    '<a href="' + detailUrl + '" class="text-decoration-none">' + posterHtml + '</a>' +
                    '<div class="card-body d-flex flex-column">' +
                        '<div class="d-flex align-items-center mb-1">' +
                            '<span class="badge bg-secondary me-1">' + it.source + '</span>' +
                            '<span class="badge bg-dark border text-capitalize">' + it.type + '</span>' +
                        '</div>' +
                        '<h6 class="card-title text-truncate mb-1">' +
                            '<a href="' + detailUrl + '" class="text-white text-decoration-none">' + it.title + '</a>' +
                        '</h6>' +
                        '<p class="card-text text-secondary small mb-2">Year: ' + it.year + '</p>' +
                        '<div class="mt-auto d-flex gap-2">' +
                            '<a href="' + detailUrl + '" class="btn btn-sm btn-outline-info flex-grow-1">Details</a>' +
                            '<button class="btn btn-sm btn-outline-warning add-fav-btn" ' +
                                'data-source="' + it.source + '" ' +
                                'data-id="' + it.id + '" ' +
                                'data-title="' + it.title.replace(/"/g, '&quot;') + '" ' +
                                'data-year="' + it.year + '" ' +
                                'data-poster="' + it.poster + '">⭐</button>' +
                        '</div>' +
                    '</div>' +
                '</div>' +
            '</div>';

        $("#genre-results").append(cardHtml);
    }
}
