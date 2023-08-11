<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Свежие новости</title>
</head>
<body>
  <h1>Свежие новости</h1>
  <form method="post">
    <label for="keywords">Введите ваш запрос или несколько через запятую:</label><br>
    <input type="text" id="keywords" name="keywords"><br>
    <input type="submit" value="Search">
  </form>

  <?php
  $newsurls = array(
      'Kommersant' => 'https://www.kommersant.ru/RSS/news.xml',
      'Lenta.ru' => 'https://lenta.ru/rss/',
      'Vesti' => 'https://www.vesti.ru/vesti.rss'
  );

  $f_all_news = 'allnews.csv';
  $f_certain_news = 'currentnews.csv';

  function parseRSS($rss_url) {
      $feed = simplexml_load_file($rss_url);
      return $feed;
  }

  function getHeadlines($rss_url) {
      $headlines = array();
      $feed = parseRSS($rss_url);
      foreach ($feed->channel->item as $item) {
          $headlines[] = $item->title;
      }
      return $headlines;
  }

  function getDescriptions($rss_url) {
      $descriptions = array();
      $feed = parseRSS($rss_url);
      foreach ($feed->channel->item as $item) {
          $descriptions[] = $item->description;
      }
      return $descriptions;
  }

  function getLinks($rss_url) {
      $links = array();
      $feed = parseRSS($rss_url);
      foreach ($feed->channel->item as $item) {
          $links[] = $item->link;
      }
      return $links;
  }

  function getDates($rss_url) {
      $dates = array();
      $feed = parseRSS($rss_url);
      foreach ($feed->channel->item as $item) {
          $dates[] = $item->pubDate;
      }
      return $dates;
  }

  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      // Get user input
      $keywords = isset($_POST['keywords']) ? $_POST['keywords'] : '';
      $keywords_arr = explode(',', $keywords);

      // Get all news
      $allheadlines = array();
      $alldescriptions = array();
      $alllinks = array();
      $alldates = array();

      foreach ($newsurls as $key => $url) {
          $allheadlines = array_merge($allheadlines, getHeadlines($url));
          $alldescriptions = array_merge($alldescriptions, getDescriptions($url));
          $alllinks = array_merge($alllinks, getLinks($url));
          $alldates = array_merge($alldates, getDates($url));
      }

      $header = array('Title', 'Description', 'Links', 'Publication Date');

      $handle = fopen($f_all_news, 'w');
      fputcsv($handle, $header);

      for ($i = 0; $i < count($allheadlines); $i++) {
          fputcsv($handle, array(
              $allheadlines[$i],
              $alldescriptions[$i],
              $alllinks[$i],
              $alldates[$i]
          ));
      }

      fclose($handle);

      // Find and write certain news
      $certain_news = array();
      foreach ($allheadlines as $i => $headline) {
          foreach ($keywords_arr as $keyword) {
              if (stripos($alldescriptions[$i], trim($keyword)) !== false) {
                  $certain_news[] = array(
                      'Title' => $headline,
                      'Description' => $alldescriptions[$i],
                      'Links' => $alllinks[$i],
                      'Publication Date' => $alldates[$i]
                  );
                  break;
              }
          }
      }

      $handle = fopen($f_certain_news, 'w');
      fputcsv($handle, $header);

      foreach ($certain_news as $row) {
fputcsv($handle, $row);
  }
    fclose($handle);

  // Redirect to the result page
  header('Location: result.php');
  exit();
}
?>
</body>
</html>

