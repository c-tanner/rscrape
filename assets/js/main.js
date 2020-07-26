function updateMedia(data) {
    html = '<div class="row mb-4 content-row">'
    if ($(data).length < 1) {
        $("#no-results").show()
    } else {
        $("#no-results").hide()
        $(data).each(function(i, val) {
            if (val.upvotes.toString().length > 6) {
                upvotes = val.upvotes[0]+'.'+val.upvotes[1]+val.upvotes[2]+'M'
            } else if (val.upvotes.toString().length <= 6 && val.upvotes.toString().length > 3) {
                upvotes = val.upvotes.toString().split('').slice(0, -3).join('')+'K'
            } else {
                upvotes = val.upvotes
            }
            if (i !== 0 && i % 3 == 0) {
                html += '</div><div class="row mb-4 content-row">'
            }
            html += '<div class="col-md-4 content-col mb-4">'
            //html += '<a href="https://reddit.com/r/'+val.subreddit+'" target="_blank">r/'+ val.subreddit +'</a><br /><br />'
            html += '<img src="./media/'+ val.filename +'" /><br />'
            html += '<div class="row" style="align-items: center">'
            html += '<div class="col-md-3">'
            html += '<i class="fal fa-plus text-success" style="font-size: 0.7rem" aria-hidden="true"></i>&nbsp;'
            html += '<span class="text-success">'+ upvotes +'</span></div>'
            html += '<div class="col-md-6">'
            html += '<a href="./media/'+ val.filename +'">'
            html += '<button type="button" class="btn btn-sm btn-light fullwidth text-uppercase download-btn">'
            html += '<i class="fal fa-download" aria-hidden="true"></i>&nbsp;&nbsp;Download'
            html += '</button>'
            html += '</a>'
            html += '</div>'
            html += '<div class="col-md-3">'
            html += '<i class="fal fa-clock" aria-hidden="true"></i>&nbsp;'+ val.elapsed +'</div>'
            html += '</div>'
            html += '</div>'
        })
        html += '</div>'
    }
    $("#content-container").html(html)
}
$(document).ready(function() {
    $.getJSON("api/getMedia.php", function(data) {
        updateMedia(data)
    })
    $("#filter-form select").on("change", function() {
        sub_filter = $("select[name='filter_sub").val()
        type_filter = $("select[name='filter_type']").val()
        if (sub_filter !== "" || type_filter !== "") {
            $.getJSON("api/getMedia.php?sub_filter="+sub_filter+"&type_filter="+type_filter, function(data) {
                updateMedia(data)
            })
        }
    })
    $.getJSON("api/getAvailable.php", function(data) {
        html = ""
        $(data).each(function(i, val) {
            html += '<option value="'+val+'">'+val+'</option>'
        })
        $("select[name='filter_sub']").append(html)
    })
    $.getJSON("api/lastUpdated.php", function(data) {
        if (typeof data.mins !== undefined) {
            $("#last-updated").html(data.mins)
        }
    })
})