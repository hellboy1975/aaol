<?php



use Doctrine\DBAL\Connection;

/**
 * class PostsModel
 */
class PostsModel extends BaseModel
{
	/**
	 * Returns an arraay of posts
	 * @param  string $type The type of posts to fetch (defaults to NEWS)
	 * @param  integer $count
	 * @param  string $order
	 * @param  integer $user
	 * @return array
	 */
	public function getPosts($category = "NEWS"/*, $count = 10, $order = "DATE_DESC", $user = 1*/)
	{
		//$sql = "SELECT * FROM post WHERE category = ? AND display = 1";
		$sql = "SELECT title, content, category, time, allow_comments, username
					FROM post
					JOIN user ON user.id = post.user_id
					WHERE category =  ?
					AND display =1";
    	return $this->db->fetchAll($sql, array((string) $category));
	}
}

