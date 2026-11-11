@extends('layouts.app')

@section('content')
<div class="container">
  <div class="page-header">
    <h2 style="margin:0;">Leave a Review</h2>
    <span style="color:var(--muted); font-size:0.85rem;">Review for {{ $ratedUser->Full_Name }}</span>
  </div>

  <div class="card" style="max-width:600px;">
    <form method="POST" action="{{ route('assignments.review', $assignment->Assignment_ID) }}">
      @csrf
      <div class="form-group">
        <label>Rating (1-5)</label>
        <input type="hidden" name="rating" id="review-rating-value" value="5">
        <div id="review-rating-stars" class="star-rating" style="display:flex; gap:4px; margin-bottom:8px;">
          <button type="button" class="star active" onclick="setReviewRating(1)" title="1 Star">★</button>
          <button type="button" class="star" onclick="setReviewRating(2)" title="2 Stars">★</button>
          <button type="button" class="star" onclick="setReviewRating(3)" title="3 Stars">★</button>
          <button type="button" class="star" onclick="setReviewRating(4)" title="4 Stars">★</button>
          <button type="button" class="star" onclick="setReviewRating(5)" title="5 Stars">★</button>
        </div>
        <div style="font-size:0.75rem; color:var(--muted); margin-bottom:12px;">Rate from 1 to 5 stars</div>
      </div>
      <div class="form-group">
        <label>Comment</label>
        <textarea name="comment" rows="4" placeholder="Write a review about this user (optional)..."></textarea>
      </div>
      <div style="display:flex; gap:10px; margin-top:16px;">
        <button type="submit" class="btn btn-primary">Submit Review</button>
        <a href="{{ route('reviews.index') }}" class="btn btn-secondary">Cancel</a>
      </div>
    </form>
  </div>
</script>
<script>
  function setReviewRating(value) {
    document.getElementById('review-rating-value').value = value;
    document.querySelectorAll('#review-rating-stars .star').forEach((star, idx) => {
      star.classList.toggle('active', idx < value);
    });
  }
</script>
@endsection
