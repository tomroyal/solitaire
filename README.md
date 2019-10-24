# Solitaire
The Solitaire cypher in verbose PHP

I've been re-reading 1990s novels on the train of late, including, most recently, [Cryptonomicon](https://en.wikipedia.org/wiki/Cryptonomicon).

Something I'd forgotten, or maybe skipped over on first reading, was the appendix by Bruce Schneier detailing the [Solitaire](https://www.schneier.com/academic/solitaire/) cipher he devised for the book. Try as I might, I couldn't quite wrap my brain around how it should be performed.

So - what better way to learn than to write the code out in a way that I could visualise the movement of the cards? And this is the result - a set of PHP functions you can use to encrypt or decrypt in Solitaire, showing the deck at each stage. I've checked it using a few of the [test vectors](https://www.schneier.com/code/sol-test.txt).

I've also left the steps explained, there are lots of comments, and I've tried to write it for legibility rather than brevity. 

For a prettier version with images, [try my online version here](https://solitairephp.herokuapp.com) - note that this is a Heroku free dyno, so might take a second or two to start up.

[tomroyal.com](https://www.tomroyal.com) / [@tomroyal](https://www.twitter.com/tomroyal)
